<?php

class Template {

    public function __construct() {

        // Add navigation
        register_nav_menus(array(
            'nav-primary' => 'Primary Navigation',
        ));

        // Add support for post thumbnails (used for the 'slideshow' post type)
        add_theme_support('post-thumbnails');

        // Add widgets
        add_action('widgets_init', function () {

            register_sidebar(array(
                'name' => 'Language Navigation',
                'id' => 'nav-language',
                'before_widget' => '',
                'after_widget' => '',
                'before_title' => '',
                'after_title' => '',
            ));

            register_sidebar(array(
                'name' => 'Footer Column 1',
                'id' => 'footer-col1',
                'before_widget' => '',
                'after_widget' => '',
                'before_title' => '<h4>',
                'after_title' => '</h4>',
            ));

            register_sidebar(array(
                'name' => 'Footer Column 2',
                'id' => 'footer-col2',
                'before_widget' => '',
                'after_widget' => '',
                'before_title' => '<h4>',
                'after_title' => '</h4>',
            ));

            register_sidebar(array(
                'name' => 'Footer Column 3',
                'id' => 'footer-col3',
                'before_widget' => '',
                'after_widget' => '',
                'before_title' => '<h4>',
                'after_title' => '</h4>',
            ));

            register_sidebar(array(
                'name' => 'Footer Content Info',
                'id' => 'footer',
                'before_widget' => '',
                'after_widget' => '',
                'before_title' => '',
                'after_title' => '',
            ));

        });

        // Format content
        add_filter('the_content', function ($content) {

            // Replace read more link
            $content = preg_replace('/<p>.*href="(.*)#more-[^"].*class="more-link">.*<\/p>/', '<p><a class="btn" href="$1">' . __('Read more', 'fiftyfirst') . '</a></p>', $content);

            // Generate <figure>
            $content = preg_replace_callback('/\[caption.*?id=".*?(\d+)".*?align="(.*?)".*?](.*?)\[\/caption\]/', function ($matches) {

                $document = new DOMDocument();
                $document->loadHTML('<?xml encoding="UTF-8">' . $matches[3]);

                $caption = trim($document->textContent);

                $xpath = new DOMXPath($document);

                $img = $xpath->query('//img')->item(0);

                $url = wp_get_attachment_image_src($matches[1], 'large')[0];

                if ($url) :

                    $string = <<<HTML
<figure class="image {$matches[2]}">
    <a href="{$url}">
        <img src="{$img->getAttribute('src')}" alt="{$img->getAttribute('alt')}">
    </a>
    <figcaption>{$caption}</figcaption>
</figure>
HTML;

                else :

                    $string = <<<HTML
<figure class="image {$matches[2]}">
    <img src="{$img->getAttribute('src')}" alt="{$img->getAttribute('alt')}">
    <figcaption>{$caption}</figcaption>
</figure>
HTML;

                endif;

                return $string;

            }, $content);

            return $content;

        }, 10, 1);

        // Set JPEG quality to 25 - we serve large images which get downsizes in
        // the browser.
        add_filter('wp_editor_set_quality', function ($quality) {
            return 25;
        });
        add_filter('jpeg_quality', function ($quality) {
            return 25;
        });

        // Setup l10n
        add_action('after_setup_theme', function () {
            load_theme_textdomain('fiftyfirst', get_template_directory() . '/languages');
        });

        /**
         * Clean up the HTML from Contact Form 7
         */
        add_filter('wpcf7_form_elements', function ($content) {

            // Remove p, br and span tags
            $content = preg_replace('/<\/?p>|<br ?\/?>|<span.*?>(.*?)<\/span>/', '$1', $content);

            // Add required attribute to input|textarea|select elements
            $content = preg_replace('/(<(input|textarea|select).*?aria-required="true".*?)(\/?>)/', '$1 required$3', $content);

            // Add classes to submit button
            $content = preg_replace('/(<input.*?type="submit".*?class=").*?(".*?>)/', '<div class="submit">$1btn btn-large$2</div>', $content);

            // Format error messages
            $content = preg_replace('/<span.*?not-valid-tip.*?>(.*?)<\/span>/', '<p class="inline-error">$1</p>', $content);

            return $content;

        });

        add_filter('wpcf7_form_response_output', function ($content) {

            // Format success message
            $content = preg_replace('/<div.*?mail-sent-ok.*?>(.*?)<\/div>/', '<p class="alert alert-success">$1</p>', $content);

            // Remove validation error message
            $content = preg_replace('/<div.*?validation-errors.*?<\/div>/', '', $content);

            return $content;

        });

        $this->cleanHead();
        $this->gallery();
        $this->customPostTypes();
        $this->cleanAdminArea();

    }

    /**
     * Cleans up varius WordPress meta tags
     */
    private function cleanHead() {

        remove_action('wp_head', 'noindex');
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
        remove_action('wp_head', 'locale_stylesheet');
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'rel_canonical');
        remove_action('wp_head', 'wp_print_styles', 8);
        remove_action('wp_head', 'wp_print_head_scripts', 9);
        remove_action('wp_enqueue_scripts', 'wpcf7_enqueue_scripts');
        remove_action('wp_enqueue_scripts', 'wpcf7_enqueue_styles');
        remove_action('wp_enqueue_scripts', 'wpseo_admin_bar_css');
        remove_action('wp_enqueue_scripts', 'wpcf7_html5_fallback');

        add_action('widgets_init', function () {

            global $wp_widget_factory;

            remove_action('wp_head', array(
                $wp_widget_factory->widgets['WP_Widget_Recent_Comments'],
                'recent_comments_style',
            ));

        });

        show_admin_bar(false);

    }

    /**
     * Print secondary navigation menu
     *
     * @return mixed
     */
    static public function navSecondary() {

        // First try child menu items
        $input = wp_nav_menu(array(
            'menu' => 'Primary Navigation',
            'container' => '',
            'items_wrap' => '%3$s',
            'theme_location' => 'nav-primary',
            'echo' => false,
        ));

        $document = new DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8">' . $input);
        $xpath = new DOMXPath($document);

        // Select the menu
        $menuItems = $xpath->query('//li[contains(@class, "current_page_ancestor") or contains(@class, "current-page-ancestor") or contains(@class, "current_page_item") or contains(@class, "current-page-item")]//ul');

        // Try and see if the page has a parent (for on the blog post page)
        if (!$menuItems->length) {
            $menuItems = $xpath->query('//li[contains(@class, "current_page_parent")]/../../ul');
        }

        // Try child pages
        if (!$menuItems->length) {

            $input = wp_list_pages(array(
                'title_li' => '',
                'echo' => false,
            ));

            $document = new DOMDocument();
            $document->loadHTML('<?xml encoding="UTF-8">' . $input);
            $xpath = new DOMXPath($document);

            // Select the menu
            $menuItems = $xpath->query('//li[contains(@class, "current_page_ancestor") or contains(@class, "current-page-ancestor") or contains(@class, "current_page_item") or contains(@class, "current-page-item")]//ul');

        }

        // Try and see if the page has a parent (for on the blog post page)
        if (!$menuItems->length) {
            $menuItems = $xpath->query('//li[contains(@class, "current_page_parent")]/../../ul');
        }

        // Stop here if nothing was found
        if (!$menuItems->length) {
            return false;
        }

        return self::renderNavSecondary($document, $menuItems, null, array('select'));

    }

    /**
     * Renders an unordered list and an select box from the supplied
     * DOMDocument object and DOMNodeList object.
     *
     * @return mixed              string with menu, false on failure
     */
    static private function renderNavSecondary(
        $document,
        $menuItems,
        $chooseText = null,
        $elements = array('ul', 'select')
    ) {

        if (
            !is_object($document) &&
            get_class($document) != 'DOMDocument' &&
            !is_object($menuItems) &&
            get_class($menuItems) != 'DOMNodeList'
        ) {
            return false;
        }

        if (!$chooseText) {
            $chooseText = __('Choose page', 'fiftyfirst');
        }

        // Append the UL menu to a document fragment
        $ulFragment = $document->createDocumentFragment();
        $ulFragment->appendChild($menuItems->item(0));

        // Remove DOCTYPE
        $document->removeChild($document->firstChild);

        // Remove XML declaration
        $document->removeChild($document->firstChild);

        // Remove everything else
        $document->removeChild($document->firstChild);

        // Append the UL
        $document->appendChild($ulFragment);

        // Build the OPTION elements
        if (in_array('select', $elements)) {

            $optionsFragment = $document->createDocumentFragment();

            $listItems = $document->getElementsByTagName('li');

            $optionElement = $document->createElement('option', '- ' . $chooseText . ' -');
            $optionsFragment->appendChild($optionElement);

            foreach ($listItems as $listItem) {

                $text = $listItem->parentNode->parentNode->parentNode ? '&mdash;&nbsp;' : '';
                $text .= htmlentities($listItem->firstChild->textContent);
                $url = $listItem->getElementsByTagName('a')->item(0)->getAttribute('href');

                $optionElement = $document->createElement('option', $text);
                $optionElement->setAttribute('value', $url);

                if (preg_match('/current(_page_item|-page-item|_page_parent|-cat)/', $listItem->getAttribute('class'))) {
                    $optionElement->setAttribute('selected', 'selected');
                }

                $optionsFragment->appendChild($optionElement);

            }
            // Build the SELECT element
            $selectElement = $document->createElement('select');
            $selectElement->setAttribute('name', 'nav-secondary');
            $selectElement->setAttribute('data-bind', 'nav-secondary');

            // Append the SELECT and OPTIONs
            $document->appendChild($selectElement)->appendChild($optionsFragment);

        }

        if (!in_array('ul', $elements)) {

            $ul = $document->getElementsByTagName('ul')->item(0);
            $ul->parentNode->removeChild($ul);

        }

        $output = '<nav class="nav-secondary clear">';
        $output .= $document->saveHTML();
        $output .= '</nav>';

        return $output;

    }

    /**
     * Social buttons
     */
    static public function social() {

        global $post;

        $url = get_permalink();

        return <<<HTML

<ul class="social clear">
    <li><div class="fb-like" data-href="{$url}" data-send="false" data-layout="button_count" data-width="140" data-show-faces="false"></div></li>
    <li><a href="https://twitter.com/share" class="twitter-share-button" data-url="{$url}" data-lang="da"></a></li>
</ul>

HTML;

    }

    /**
     * Gallery
     */
    private function gallery() {

        // Gallery shortcode
        add_filter('post_gallery', function ($output, $attr) {

            global $post;

            if (isset($attr['orderby'])) {

                $attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
                if (!$attr['orderby']) {
                    unset($attr['orderby']);
                }

            }

            extract(shortcode_atts(array(
                'order' => 'ASC',
                'orderby' => 'menu_order ID',
                'id' => $post->ID,
                'include' => '',
                'exclude' => ''
            ), $attr));

            if ('RAND' == $order) {
                $orderby = 'none';
            }

            if (!empty($include)) {

                $_attachments = get_posts(array(
                    'include' => $include,
                    'post_status' => 'inherit',
                    'post_type' => 'attachment',
                    'post_mime_type' => 'image',
                    'order' => $order,
                    'orderby' => $orderby
                ));

                $attachments = array();

                foreach ($_attachments as $key => $val) {
                    $attachments[$val->ID] = $_attachments[$key];
                }

            } elseif (!empty($exclude)) {

                $attachments = get_children(array(
                    'post_parent' => $id,
                    'exclude' => $exclude,
                    'post_status' => 'inherit',
                    'post_type' => 'attachment',
                    'post_mime_type' => 'image',
                    'order' => $order,
                    'orderby' => $orderby
                ));

            } else {

                $attachments = get_children(array(
                    'post_parent' => $id,
                    'post_status' => 'inherit',
                    'post_type' => 'attachment',
                    'post_mime_type' => 'image',
                    'order' => $order,
                    'orderby' => $orderby
                ));

            }

            if (empty($attachments)) {
                return '';
            }

            $columnType = count($attachments) >= 4 ? 25 : 33;

            $output = '<div class="gallery">';
            $output .= '<ul class="columns clear">';

            foreach ($attachments as $id => $attachment) {

                $src = wp_get_attachment_image_src($id, 'medium')[0];
                $url = wp_get_attachment_image_src($id, 'large')[0];

                $output .= <<<HTML
<li class="column column-{$columnType}">
    <figure>
        <a href="{$url}">
            <img src="{$src}" alt="{$attachment->post_excerpt}">
        </a>
        <figcaption>{$attachment->post_excerpt}</figcaption>
    </figure>
</li>
HTML;

            }

            $output .= '</ul>';
            $output .= '</div>';

            return $output;

        }, 10, 2);

    }

    /**
     * Custom post type
     */
    private function customPostTypes() {

        // Add 'slideshow' custom post type
        add_action('init', function () {

            $labels = array(
                'name' => _x('Slideshow', 'post type general name'),
                'singular_name' => _x('Slide', 'post type singular name'),
                'add_new' => _x('Add Slide', 'Slideshow'),
                'add_new_item' => __('Add New Slide'),
                'edit_item' => __('Edit Slide'),
                'new_item' => __('New Slide'),
                'all_items' => __('All Slides'),
                'view_item' => __('View Slide'),
                'search_items' => __('Search Slides'),
                'not_found' => __('No slides found'),
                'not_found_in_trash' => __('No slides found in the Trash'),
                'parent_item_colon' => '',
                'menu_name' => 'Slideshow',
            );

            $args = array(
                'labels' => $labels,
                'description' => 'All slides for the frontpage slideshow.',
                'public' => true,
                'hierarchical' => true,
                'menu_position' => 20,
                'supports' => array('title', 'thumbnail'),
            );

            register_post_type('slideshow', $args);

        }, 0);

        // Post thumbnail meta box settings
        add_action('do_meta_boxes', function () {

            // Remove post and page thumbnail meta boxes
            remove_meta_box('postimagediv', 'page', 'side');

            // Move meta box to main column for 'slideshow' post type
            remove_meta_box('postimagediv', 'slideshow', 'side');
            add_meta_box('postimagediv', __('Image'), 'post_thumbnail_meta_box', 'slideshow', 'normal', 'low');

        });

        // Add link meta box to 'slideshow' post type
        add_action('add_meta_boxes', function () {

            add_meta_box('slideshow_sectionid', __('Link', 'slideshow_textdomain'), function ($post) {

                // Use nonce for verification
                wp_nonce_field(__FILE__, 'slideshow_noncename');

                // The actual fields for data entry
                // Use get_post_meta to retrieve an existing value from the database and use the value for the form
                $slideshowUrl = get_post_meta($post->ID, '_slideshow_url', true);

                $slideshowUrl = !empty($slideshowUrl) ? $slideshowUrl : 'http://';

                echo '<p><label for="slideshowurl"><strong>URL</strong></label></p>';
                echo '<p><input class="regular-text code" type="text" id="slideshowurl" name="slideshowurl" value="' . $slideshowUrl . '"/></p>';

                // The actual fields for data entry
                // Use get_post_meta to retrieve an existing value from the database and use the value for the form
                $slideshowPosition = get_post_meta($post->ID, '_slideshow_position', true);

                $slideshowPosition = !empty($slideshowPosition) ? $slideshowPosition : 'topleft';

                echo '<table width="100%"><tr>';
                echo '<td colspan="2"><strong>Caption Position</strong></td>';
                echo '<tr/><tr>';
                echo '<td><label><input type="radio" name="slideshowposition" value="topleft"' . ($slideshowPosition == 'topleft' ? ' checked="checked"' : '') . '/> Top left</label></td>';
                echo '<td><label><input type="radio" name="slideshowposition" value="topright"' . ($slideshowPosition == 'topright' ? ' checked="checked"' : '') . '/> Top right</label><br/></td>';
                echo '<tr/><tr>';
                echo '<td><label><input type="radio" name="slideshowposition" value="bottomleft"' . ($slideshowPosition == 'bottomleft' ? ' checked="checked"' : '') . '/> Bottom left</label></td>';
                echo '<td><label><input type="radio" name="slideshowposition" value="bottomright"' . ($slideshowPosition == 'bottomright' ? ' checked="checked"' : '') . '/> Bottom right</label></td>';
                echo '</table></tr>';


            }, 'slideshow', 'normal', 'high');

        });

        // Save custom meta boxes
        add_action('save_post', function () {

            if (!isset($_POST['post_type'])) {
                return;
            }

            // First we need to check if the current user is authorised to do this action.
            if ($_POST['post_type'] == 'slideshow') {

                if (!current_user_can('edit_page', $post_id)) {
                    return;
                }

            }

            $postId = $_POST['post_ID'];

            // Secondly we need to check if the user intended to change this value.
            if (isset($_POST['slideshow_noncename']) && wp_verify_nonce($_POST['slideshow_noncename'], __FILE__ )) {

                // Sanitize user input
                $slideshowUrl = sanitize_text_field($_POST['slideshowurl']);
                $slideshowPosition = sanitize_text_field($_POST['slideshowposition']);

                // Save data
                add_post_meta($postId, '_slideshow_url', $slideshowUrl, true) or update_post_meta($postId, '_slideshow_url', $slideshowUrl);
                add_post_meta($postId, '_slideshow_position', $slideshowPosition, true) or update_post_meta($postId, '_slideshow_position', $slideshowPosition);

            } else {
                return;
            }

        });

        // Flush rewrite rules to get custom post types working after theme switch
        add_action('after_switch_theme', function () {
            flush_rewrite_rules();
        });

    }

    /**
     * Format links with anchor tags
     *
     * @param  string $content
     * @return string
     */
    static public function formatLinks($content) {

        $content = preg_replace('/(https?\:\/\/((\w|\d|\-|\.)+\.\w{2,3})(\/\S*)?)/i', '<a href="$1" target="_blank">$2</a>', $content);

        return $content;
    }

    /**
     * Use this method to strip out the <div class="textwidget"></div>
     *
     * @param  string $id Sidebar ID
     * @return string
     */
    static public function dynamicSidebar($id) {

        ob_start();

        $html = '';

        if (dynamic_sidebar($id)) {

            $str = ob_get_contents();

            $document = new DOMDocument();
            $document->loadHTML('<?xml encoding="UTF-8">' . $str);

            $xpath = new DOMXPath($document);

            $nodes = $xpath->query('//h4|//div[@class="textwidget"]/*');

            foreach ($nodes as $node) {
                $html .= $node->ownerDocument->saveHTML($node);
            }

        }

        ob_end_clean();

        return $html;

    }

    /**
     * Cleans up the admin area
     */
    private function cleanAdminArea() {

        // Remove unused admin meta boxes
        add_action('admin_menu', function () {

            remove_meta_box('postcustom', 'post', 'normal');
            remove_meta_box('postcustom', 'page', 'normal');
            remove_meta_box('postexcerpt', 'post', 'normal');
            remove_meta_box('postexcerpt', 'page', 'normal');
            remove_meta_box('commentsdiv', 'post', 'normal');
            remove_meta_box('commentsdiv', 'page', 'normal');
            remove_meta_box('tagsdiv-post_tag', 'post', 'side');
            remove_meta_box('pll-tagsdiv-post_tag', 'post', 'side');
            remove_meta_box('tagsdiv-post_tag', 'page', 'side');
            remove_meta_box('pll-tagsdiv-post_tag', 'page', 'side');
            remove_meta_box('trackbacksdiv', 'post', 'normal');
            remove_meta_box('trackbacksdiv', 'page', 'normal');
            remove_meta_box('commentstatusdiv', 'post', 'normal');
            remove_meta_box('commentstatusdiv', 'page', 'normal');
            remove_meta_box('dashboard_recent_comments', 'dashboard', 'core');
            remove_meta_box('dashboard_incoming_links', 'dashboard', 'core');
            remove_meta_box('dashboard_plugins', 'dashboard', 'core');
            remove_meta_box('dashboard_primary', 'dashboard', 'core');
            remove_meta_box('dashboard_secondary', 'dashboard', 'core');

        });

        // Remove unused admin menu items
        add_action('admin_menu', function () {

            global $menu, $submenu;

            $restricted = array(__('Posts'), __('Comments'));
            end($menu);

            while (prev($menu)) {
                $value = explode(' ', $menu[key($menu)][0]);
                if (in_array($value[0] != null ? $value[0] : '', $restricted)) {
                    unset($menu[key($menu)]);
                }
            }

            unset($submenu['edit.php'][16]);
            unset($submenu['options-general.php'][25]);

        });

        // Remove Categories and Comments columns from Posts
        add_filter('manage_post_posts_columns', function ($columns) {

            unset($columns['tags']);
            unset($columns['comments']);

            return $columns;

        }, 10, 1);

        // Remove Comments column from Pages
        add_filter('manage_page_posts_columns', function ($columns) {

            unset($columns['comments']);

            return $columns;

        }, 10, 1);

        // Add credits to admin footer
        add_filter('admin_footer_text', function () {
             return 'Design and code by <a href="http://fiftyfir.st">51<sup>st</sup></a> | Powered by <a href="http://www.wordpress.org">WordPress</a>';
        });

        // Add editor styling
        add_action('init', function () {
            add_editor_style('css/editor-style.css');
        });

    }

}

<!DOCTYPE html>
<!--[if IE 8]><html class="no-js lt-ie10 lt-ie9" <?php language_attributes(); ?>><![endif]-->
<!--[if IE 9]><html class="no-js lt-ie10" <?php language_attributes(); ?>><![endif]-->
<!--[if gt IE 9]><!--><html class="no-js" <?php language_attributes(); ?>><!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?php wp_title('&ndash;', true, 'right'); ?></title>
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <!-- build:css <?php bloginfo('template_url'); ?>/css/style.css -->
        <link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/css/style.css">
        <!-- endbuild -->
        <!-- build:js <?php bloginfo('template_url'); ?>/js/modernizr.js -->
        <script src="<?php bloginfo('template_url'); ?>/components/modernizr/modernizr.js"></script>
        <!-- endbuild -->
        <meta name="robots" content="index,follow">
        <?php wp_head(); ?>
        <link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?>" href="<?php bloginfo('rss2_url'); ?>">
    </head>
    <body class="clear">
        <div id="fb-root"></div>

        <div class="container">

            <header class="header clear" role="banner">
                <nav class="nav-primary clear" role="navigation">
                    <ul>
                        <li class="logo"><a href="<?= WP_HOME ?>"><img src="<?php bloginfo('template_url'); ?>/images/logo.png" alt="<?php bloginfo('name'); ?>"></a></li>
                        <li class="menu"><a href="#" data-bind="toggle-nav-primary">Menu</a></li>
                        <?php
                        wp_nav_menu(array(
                            'menu' => 'Primary navigation',
                            'container' => '',
                            'items_wrap' => '%3$s',
                            'theme_location' => 'nav-primary',
                            'depth' => 2,
                        ));
                        ?>
                    </ul>
                </nav>
                <nav class="nav-language clear">
                    <?php dynamic_sidebar('nav-language'); ?>
                </nav>
                <?= Template::navSecondary(); ?>
            </header>

            <div class="main clear" role="main">

            </div>
        </div>

        <footer>

            <div class="footer" role="contentinfo">
                <div class="columns clear">
                    <div class="column column-33">
                        <?= Template::dynamicSidebar('footer-col1'); ?>
                    </div>
                    <div class="column column-33">
                        <?= Template::dynamicSidebar('footer-col2'); ?>
                    </div>
                    <div class="column column-33">
                        <?= Template::dynamicSidebar('footer-col3'); ?>
                    </div>
                </div>
                <?= Template::dynamicSidebar('footer'); ?>
            </div>

        </footer>

        <!-- build:js <?php bloginfo('template_url'); ?>/js/main.js -->
        <script src="<?php bloginfo('template_url'); ?>/components/jquery/jquery.js"></script>
        <script src="<?php bloginfo('template_url'); ?>/js/main.js"></script>
        <!-- endbuild -->

    </body>
</html>

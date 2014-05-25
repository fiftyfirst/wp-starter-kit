(function ($, Modernizr, window, document) {

    'use strict';

    $(function () {

        /**
         * Change page on sections select change
         */
        $('[data-bind="nav-secondary"]').change(function () {

            if (this.value.indexOf('http://') !== -1) {
                window.location = this.value;
            }

        });

    });

})(jQuery, Modernizr, window, document);

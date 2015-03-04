define(
    ['jquery', 'pim/initselect2', 'wysiwyg', 'bootstrap', 'bootstrap.bootstrapswitch'],
    function ($, initSelect2, wysiwyg) {
        'use strict';

        return function ($target) {
            // Apply Select2
            initSelect2.init($target);

            // Apply bootstrapSwitch
            $target.find('.switch:not(.has-switch)').bootstrapSwitch();

            // Initialize tooltip
            $target.find('[data-toggle="tooltip"]').tooltip();

            // Initialize popover
            $target.find('[data-toggle="popover"]').popover();

            // Activate a form tab
            $target.find('li.tab.active a').each(function () {
                var paneId = $(this).attr('href');
                $(paneId).addClass('active');
            });

            $target.find('textarea.wysiwyg[id]:not([aria-hidden])').each(function () {
                if (!$(this).closest('.attribute-field').hasClass('scopable')) {
                    wysiwyg.init($(this));
                }
            });
        };
    }
);


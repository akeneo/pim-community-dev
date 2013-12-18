define(
    ['jquery', 'pim/initselect2', 'bootstrap', 'bootstrap.bootstrapswitch', 'bootstrap-tooltip'],
    function ($, initSelect2) {
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
        };
    }
);


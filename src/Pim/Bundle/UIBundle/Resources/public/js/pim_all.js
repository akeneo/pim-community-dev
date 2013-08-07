$(function() {
    'use strict';
    Oro.Navigation.prototype.bind('route', runInit);
});

function runInit() {
    $(document).off('ajaxStop', init).on('ajaxStop', init);
}

function init() {
    // Place code that we need to run on every page load here
    $('.has-sidebar').sidebarize();

    $('.switch:not(.has-switch)').bootstrapSwitch();
}

$(function() {
    'use strict';
    // Do global event binding here

    // Toogle accordion icon
    $(document).on('show hide', '.accordion', function(e) {
        $(e.target).siblings('.accordion-heading').find('.accordion-toggle i').toggleClass('fa-icon-collapse-alt fa-icon-expand-alt');
    });
});

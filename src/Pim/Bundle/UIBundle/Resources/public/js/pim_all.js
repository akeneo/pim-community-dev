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

    $('form[data-updated]').each(function() {
        new FormUpdateListener($(this).attr('id'), $(this).data('updated'));
    });
}

$(function() {
    'use strict';
    // Do global event binding here

    // Toogle accordion icon
    $(document).on('show hide', '.accordion', function(e) {
        $(e.target).siblings('.accordion-heading').find('.accordion-toggle i').toggleClass('fa-icon-collapse-alt fa-icon-expand-alt');
    });
});

var FormUpdateListener = function(formId, message) {
    this.updated = false;

    this.formUpdated = function() {
        this.updated = true;
        $('#updated').show();
        $(document).off('change', 'form#' + formId, this.formUpdated);
        $(document).off('click', 'form#' + formId + ' ins.jstree-checkbox', this.formUpdated);

        // This will not work with backbone navigation
        $(window).on('beforeunload', function() {
            if (this.updated) {
                return message;
            }
        });
        $(document).on('form#' + formId + ' button[type="submit"]', 'click', function() {
            this.updated = false;
        });
    };

    $('#updated').hide();
    $(document).on('change', 'form#' + formId, this.formUpdated);
    $(document).on('click', 'form#' + formId + ' ins.jstree-checkbox', this.formUpdated);
};

var Pim = Pim || {};

Pim.navigate = function(route) {
    Oro.hashNavigationInstance.setLocation(route);
};

// Listener for form update events (used in product edit form)
Pim.updateListener = function($form) {
    this.updated = false;
    var message = $form.attr('data-updated-message'),
    title = $form.attr('data-updated-title'),
    self = this;

    var formUpdated = function() {
        self.updated = true;
        $('#updated').show();

        $form.off('change', formUpdated);
        $form.find('ins.jstree-checkbox').off('click', formUpdated);

        $form.find('button[type="submit"]').on('click', function() {
            self.updated = false;
        });

        $(window).on('beforeunload', function() {
            if (self.updated) {
                return message;
            }
        });
    };

    $form.on('change', formUpdated);
    $form.find('ins.jstree-checkbox').on('click', formUpdated);

    var linkClicked = function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        var url = $(this).attr('href');
        var doAction = function() {
            Pim.navigate(url);
        };
        if (!self.updated) {
            doAction();
        } else {
            PimDialog.confirm(message, title, doAction);
        }
        return false;
    };

    $('a[href^="/"]:not(".no-hash")').off('click').on('click', linkClicked);

    Backbone.Router.prototype.on('route', function() {
        $('a[href^="/"]:not(".no-hash")').off('click', linkClicked);
    });
};

function init() {
    // Place code that we need to run on every page load here

    // Disable the oro scrollable container
    $('.scrollable-container').removeClass('scrollable-container').css('overflow', 'visible');

    // Move scope filter to the proper location and remove it from the 'Manage filters' selector
    // TODO: Override Oro/Bundle/FilterBundle/Resources/public/js/app/filter/list.js and manage this there
    Oro.Events.once('datagrid_filters:rendered', function() {
        $('.scope-filter').parent().addClass('pull-right').insertBefore($('.actions-panel'));
        $('.scope-filter').find('select').multiselect({classes: 'select-filter-widget scope-filter-select'});

        $('#add-filter-select').find('option[value="scope"]').remove();
        $('#add-filter-select').multiselect('refresh');
    });

    // Instantiate sidebar
    $('.has-sidebar').sidebarize();

    // Apply Select2
    $('form select').select2({ allowClear: true });

    // Apply Select2 multiselect
    $('form input.multiselect').each(function() {
        $(this).select2({ tags: $(this).val() });
    });

    // Apply bootstrapSwitch
    $('.switch:not(.has-switch)').bootstrapSwitch();

    // Destroy Select2 where it's not necessary
    $('#default_channel').select2('destroy');

    // Activate a form tab
    $('li.tab.active a').each(function() {
        var paneId = $(this).attr('href');
        $(paneId).addClass('active');
    });

    // Toogle accordion icon
    $('.accordion').on('show hide', function(e) {
        $(e.target).siblings('.accordion-heading').find('.accordion-toggle i').toggleClass('fa-icon-collapse-alt fa-icon-expand-alt');
    });

    $('.remove-attribute').each(function() {
        var target = $(this).parent().find('.icons-container').first();
        if (target.length) {
            $(this).appendTo(target).attr('tabIndex', -1);
        }
    });

    $('form div.scopable').scopableField();
    $('form div.currency').currencyField();

    $('#attribute-buttons .dropdown-menu').click(function (e) {
        e.stopPropagation();
    });

    $('#default_channel').change(function() {
        $('.scopable').scopableField({ defaultScope: $(this).val() });
    });

    $('.dropdown-menu.channel a').click(function (e) {
        e.preventDefault();
        $('.scopable').scopableField($(this).data('action'));
    });

    // Add form update listener
    $('form[data-updated-message]').each(function() {
        Pim.updateListener($(this));
    });

    // Instantiate the tree
    $('[data-tree]').each(function() {
        switch ($(this).attr('data-tree')) {
            case 'associate':
                Pim.tree.associate($(this).attr('id'));
                break;
            case 'view':
                Pim.tree.view($(this).attr('id'));
                break;
            case 'manage':
                Pim.tree.manage($(this).attr('id'));
                break;
            default:
                break;
        }
    });

    // Instantiate dialogForm
    $('[data-form="dialog"]').each(function() {
        $(this).dialogForm();
    });

    // Instantiate popin form
    $('[data-form="popin"]').each(function() {
        Pim.popinForm($(this).attr('id'));
    });

    // Clean up multiselect plugin generated content that is appended to body
    $('body>.ui-multiselect-menu').appendTo($('#container'));

    // DELETE request for delete buttons
    $('[data-dialog]').on('click', function() {
        var $el = $(this);
        var message = $el.data('message');
        var title = $el.data('title');
        if ($el.data('dialog') ==  'confirm') {
            var doAction = function() {
                $el.off('click');
                var $form = $('<form>', { method: 'POST', action: $el.attr('data-url')});
                $('<input>', { type: 'hidden', name: '_method', value: $el.data('method')}).appendTo($form);
                $form.appendTo('body').submit();
            };

            PimDialog.confirm(message, title, doAction);
        } else {
            PimDialog.alert(message, title);
        }
    });

    // Save and restore activated form tabs and groups
    function saveFormState() {
        var activeTab = $('#form-navbar .nav li.active a').attr('href');
        if (activeTab) {
            sessionStorage.activeTab = activeTab;
        }

        var activeGroup = $('.tab-groups li.tab.active a').attr('href');
        if (activeGroup) {
            sessionStorage.activeGroup = activeGroup;
        }
    }

    function restoreFormState() {
        if (sessionStorage.activeTab) {
            var $activeTab = $('[href=' + sessionStorage.activeTab + ']');
            if ($activeTab) {
                $activeTab.tab('show');
            }
            sessionStorage.removeItem('activeTab');
        }

        if (sessionStorage.activeGroup) {
            var $activeGroup = $('[href=' + sessionStorage.activeGroup + ']');
            if ($activeGroup) {
                $activeGroup.tab('show');
            }
            sessionStorage.removeItem('activeGroup');
        }
    }

    if (typeof(Storage) !== 'undefined') {
        restoreFormState();

        $('form.form-horizontal').on('submit', saveFormState);
        $('#locale-switcher a').on('click', saveFormState);
    }

    // Initialize slimbox
    if (!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
        $("a[rel^='lightbox']").slimbox({
            overlayOpacity: 0.3,
        }, null, function(el) {
            return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
        });
    }

    var $localizableIcon = $('<i>', {
        'class': 'fa-icon-globe',
        'attr': {
            'data-original-title': _.__('Localized value'),
            'rel': 'tooltip'
        }
    });
    $('.attribute-field.translatable').each(function() {
        $(this).find('div.controls .icons-container').append($localizableIcon.clone());
    });

    $('[rel="tooltip"]').tooltip();

    $('form').on('change', 'input[type="file"]', function() {
        var filename = $(this).val().split('\\').pop();
        var $input = $(this);
        var $info = $input.siblings('.upload-info').first();
        var $zone = $info.parent();
        var $filename = $info.find('.upload-filename');
        var $remove = $info.find('.remove-upload');
        var $checkbox = $info.find('input[type="checkbox"]');

        var $preview = $info.find('.upload-preview');
        if ($preview.prop('tagName').toLowerCase() !== 'i') {
            var iconClass = $zone.hasClass('image') ? 'fa-icon-camera-retro' : 'fa-icon-file';
            $preview.replaceWith($('<i>', { 'class': iconClass + ' upload-preview'}));
            $preview = $info.find('.upload-preview');
        }

        if (filename) {
            $filename.html(filename);
            $zone.removeClass('empty');
            $preview.removeClass('empty');
            $remove.removeClass('hide');
            $input.attr('disabled', 'disabled').addClass('hide');
            $checkbox.removeAttr('checked');
        } else {
            $filename.html($filename.attr('data-empty-title'));
            $zone.addClass('empty');
            $preview.addClass('empty');
            $remove.addClass('hide');
            $input.removeAttr('disabled').removeClass('hide');
            $checkbox.attr('checked', 'checked');
        }
    });

    $('form').on('submit', function() {
        $('input[type="file"]').removeAttr('disabled');
    });

    $('.remove-upload').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $checkbox = $(this).siblings('input[type="checkbox"]').first();
        $checkbox.attr('checked', 'checked');
        var $input = $(this).parent().siblings('input[type="file"]');
        $input.removeAttr('disabled').removeClass('hide');
        $input.replaceWith($input.clone());

        var $info = $(this).siblings('.upload-filename');
        $info.html($info.attr('data-empty-title'));

        $(this).siblings('.upload-preview').addClass('empty');
        $(this).addClass('hide');
        $(this).parent().parent().addClass('empty');
    });
}

$(function() {
    'use strict';

    $(window).off('beforeunload');

    // Execute the init function on page load
    init();
});

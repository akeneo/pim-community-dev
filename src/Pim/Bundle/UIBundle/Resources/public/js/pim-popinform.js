define(
    ['jquery', 'underscore', 'jquery.multiselect', 'jquery.multiselect.filter', 'jquery.select2'],
    function ($, _) {

        return function (elementId) {
            'use strict';
            var $el = $('#' + elementId);
            if (!$el || !$el.length || !_.isObject($el)) {
                throw new Error('Unable to instantiate available attributes form on this element');
            }

            var opts = {
                title: $el.attr('data-title'),
                placeholder: $el.attr('data-placeholder'),
                emptyText: $el.attr('data-empty-text'),
                header: '',
                height: 175,
                minWidth: 225,
                classes: 'pimmultiselect',
                position: {
                    my: 'right top',
                    at: 'right bottom',
                    collision: 'none'
                }
            };
            opts.selectedText = opts.title;
            opts.noneSelectedText = opts.title;

            var $select = $el.find('select');
            $select.select2('destroy');

            $select.multiselect(opts).multiselectfilter({
                label: false,
                placeholder: opts.placeholder
            });

            var $menu = $('.ui-multiselect-menu.pimmultiselect').appendTo($('#container'));
            var saveButton = $el.attr('data-save-button');
            var target = $el.attr('data-target');

            var footerContainer = $('<div>').addClass('ui-multiselect-footer').appendTo($menu);
            var $saveButton = $('<a>').addClass('btn btn-small').html(saveButton).on('click', function () {
                $select.multiselect('close');
                if ($select.val() !== null) {
                    $el.submit();
                }
            }).appendTo(footerContainer);

            var $openButton = $('button.pimmultiselect').addClass('btn btn-group');
            $openButton.append($('<span>', { 'class': 'caret' })).removeAttr('style');
            if (target) {
                $openButton.prependTo($(target));
            }

            $menu.find('input[type="search"]').width(207);

            var $content = $menu.find('.ui-multiselect-checkboxes');
            if (!$content.html()) {
                $content.html(
                    $('<span>', { html: opts.emptyText, css: {
                        'position': 'absolute',
                        'color': '#999',
                        'padding': '15px',
                        'font-size': '13px'
                    }})
                );
                $saveButton.addClass('disabled');
            }
        };
    }
);

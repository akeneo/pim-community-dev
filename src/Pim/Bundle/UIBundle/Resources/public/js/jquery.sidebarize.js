/* global jQuery */

/**
 * jQuery hideable sidebar plugin
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
(function ($) {
    'use strict';

    function getState(key) {
        if (typeof Storage !== 'undefined') {
            return sessionStorage[key] || null;
        }

        return null;
    }

    function saveState(key, value) {
        if (typeof Storage !== 'undefined') {
            sessionStorage[key] = value;
        }
    }

    function getAvailableHeight($element) {
        var height = $(window).height() - $element.offset().top;
        // @todo: remove in production environment
        if ($('.sf-toolbar').length) {
            height -= $('.sf-toolbar').height() + 1;
        }

        return height;
    }

    function collapse($element, opts) {
        var $switchInputLabel = $('label[for="nested_switch_input"]');

        $('>.sidebar', $element).width(0);
        $('>.separator', $element).toggleClass('expanded collapsed').css({
            'width': opts.collapsedSeparatorWidth - 2 + 'px',
            'cursor': 'default'
        });
        $switchInputLabel.hide();
        adjustWidth($element, opts);
        $element.find('.separator i').addClass(opts.expandIcon);
        $element.find('.separator b').removeClass(opts.dragIcon);
        saveState(opts.stateStorageKey, 0);
    }

    function expand($element, opts) {
        var $switchInputLabel = $('label[for="nested_switch_input"]');

        $('>.sidebar', $element).width(parseInt(getState(opts.widthStorageKey), 10) || opts.sidebarWidth);
        $('>.separator', $element).toggleClass('expanded collapsed').css({
            'width': opts.separatorWidth - 2 + 'px',
            'cursor': opts.resizeCursor
        });
        $switchInputLabel.show();
        adjustWidth($element, opts);
        $element.find('.separator i').removeClass(opts.expandIcon);
        $element.find('.separator b').addClass(opts.dragIcon);
        saveState(opts.stateStorageKey, 1);
    }

    function adjustHeight($element) {
        var height = getAvailableHeight($element);
        $element.outerHeight(height);
    }

    function adjustWidth($element, opts) {
        var contentWidth = $(window).width();
        if ($('>.separator', $element).hasClass('collapsed')) {
            contentWidth -= opts.collapsedSeparatorWidth;
        } else {
            contentWidth -= opts.separatorWidth + $('>.sidebar', $element).width();
        }
        $('>.content', $element).width(contentWidth);
    }

    function prepare($element, opts) {
        $('body').css('overflow', 'hidden');

        var $sidebar     = $element.children().first();
        var $content     = $element.children().last();
        var sidebarWidth = parseInt(getState(opts.widthStorageKey), 10) || opts.sidebarWidth;

        $element.addClass('sidebarized').css('position', 'absolute');

        $sidebar = $sidebar.wrap($('<div>', { 'class': 'sidebar-content', 'height': '100%' }))
                           .parent().css('overflow', 'auto');
        $sidebar = $sidebar.wrap($('<div>', { 'class': 'sidebar' })).parent().width(sidebarWidth);

        $content.addClass('content').css({
            'margin-left': '0',
            'overflow-y': 'auto'
        });

        var $controls = $('<div>', {
            'class': 'sidebar-controls',
            css: opts.controlsCss,
            height: opts.controlsHeight
        }).prependTo($sidebar);

        var $separator = $('<div>', {
            'class': 'separator expanded',
            'attr': {
                unselectable: 'on'
            },
            css: opts.separatorCss
        }).css('cursor', opts.resizeCursor).css(opts.unselectableCss).width(opts.separatorWidth - 2 + 'px');

        $separator.insertAfter($sidebar).on('dblclick', function () {
            if ($(this).hasClass('collapsed')) {
                expand($element, opts);
            } else {
                collapse($element, opts);
            }
        });

        $sidebar.css(opts.childrenCss);
        $content.css(opts.childrenCss);
        $separator.css(opts.childrenCss);

        $('<i>', { 'class': opts.collapseIcon, css: opts.iconCss }).on('click', function () {
            collapse($element, opts);
        }).appendTo($controls);

        $('<i>', { css: opts.iconCss }).on('click', function () {
            expand($element, opts);
        }).appendTo($separator).hide();

        $('<b>').addClass(opts.dragIcon).css(opts.dragIconCss).appendTo($separator);

        opts.buttons.map(function (button) {
            $(button).children('.dropdown-toggle').css(opts.buttonsCss);
            $(button).css(opts.buttonsCss).appendTo($controls);
        });

        $element.find('.sidebar-list li').on('click', function () {
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
        });
    }

    $.fn.sidebarize = function (options) {
        var opts = $.extend({}, $.fn.sidebarize.defaults, options);

        return this.each(function () {
            var $element = $(this);

            if ($element.hasClass('sidebarized')) {
                return;
            }
            if ($element.children().length !== 2) {
                throw new Error('Sidebarize: the element must have 2 child elements');
            }

            prepare($element, opts);

            function doSplit(e) {
                var windowWidth = $(window).width();
                var maxWidth    = opts.maxSidebarWidth || windowWidth - opts.separatorWidth;
                var position    = e.pageX;

                position = Math.min(Math.max(position, opts.minSidebarWidth), maxWidth);

                $('>.sidebar', $element).width(position);
                $('>.content', $element).width(windowWidth - position - opts.separatorWidth);
            }

            function endSplit() {
                $(document).off('mousemove', doSplit).off('mouseup', endSplit);

                $element.children().css(opts.selectableCss);
                saveState(opts.widthStorageKey, parseInt($('>.sidebar', $element).width(), 10));
            }

            function startSplit() {
                if ($('>.separator', $element).hasClass('collapsed')) {
                    return;
                }
                $element.children().css(opts.unselectableCss);

                $(document).on('mousemove', doSplit).on('mouseup', endSplit);
            }

            $('>.separator', $element).on('mousedown', startSplit);

            if (parseInt(getState(opts.stateStorageKey), 10) === 0) {
                collapse($element, opts);
            }

            $(window).on('resize', function () {
                adjustHeight($element);
                adjustWidth($element, opts);
            });
            $(document).ajaxSuccess(function () {
                adjustHeight($element);
                adjustWidth($element, opts);
            });

            $(window).trigger('resize');
        });
    };

    $.fn.sidebarize.defaults = {
        sidebarWidth: 255,
        minSidebarWidth: 255,
        maxSidebarWidth: null,
        widthStorageKey: 'sidebar_width',
        stateStorageKey: 'sidebar_state',
        separatorWidth: 10,
        collapsedSeparatorWidth: 22,
        controlsHeight: 25,
        collapseIcon: 'icon-double-angle-left',
        expandIcon: 'icon-double-angle-right',
        dragIcon: 'icon-ellipsis-vertical',
        resizeCursor: 'e-resize',
        childrenCss: {
            'position': 'relative',
            'float': 'left',
            'height': '100%',
            'left': 0
        },
        controlsCss: {
            'border': '1px solid #ddd',
            'text-align': 'right'
        },
        separatorCss: {
            'border': '1px solid #ddd'
        },
        unselectableCss: {
            'user-select': 'none',
            '-webkit-user-select': 'none',
            '-khtml-user-select': 'none',
            '-moz-user-select': 'none'
        },
        selectableCss: {
            'user-select': 'text',
            '-webkit-user-select': 'text',
            '-khtml-user-select': 'text',
            '-moz-user-select': 'text'
        },
        dragIconCss: {
            'position': 'relative',
            'top': '48%',
            'margin-left': '1px',
            'font-size': '16px',
            'color': '#999'
        },
        iconCss: {
            'font-weight': 'bold',
            'font-size': 16,
            'color': '#999',
            'line-height': '20px',
            'float': 'right',
            'margin': '0',
            'padding': '1px 6px 0',
            'cursor': 'pointer'
        },
        buttonsCss: {
            'float': 'left',
            'height': '23px',
            'line-height': '23px'
        },
        buttons: []
    };
})(jQuery);

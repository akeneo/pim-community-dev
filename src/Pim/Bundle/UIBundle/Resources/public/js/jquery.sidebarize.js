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
        $('>.sidebar', $element).hide();
        $('>.separator', $element).toggleClass('expanded collapsed').outerWidth(opts.collapsedSeparatorWidth).css({
            'left': 0,
            'cursor': 'default'
        });
        $('>.content', $element).css('left', opts.collapsedSeparatorWidth);
        $element.find('.separator i').addClass(opts.expandIcon);
        saveState(opts.stateStorageKey, 0);
    }

    function expand($element, opts) {
        $('>.sidebar', $element).show();
        var sidebarWidth = $('>.sidebar', $element).width();
        $('>.separator', $element).toggleClass('expanded collapsed').outerWidth(opts.separatorWidth).css({
            'left': sidebarWidth,
            'cursor': opts.resizeCursor
        });
        $('>.content', $element).css('left', sidebarWidth + opts.separatorWidth);
        $element.find('.separator i').removeClass(opts.expandIcon);
        saveState(opts.stateStorageKey, 1);
    }

    function adjustHeight($element) {
        var height = getAvailableHeight($element);
        $element.outerHeight(height);
    }

    function prepare($element, opts) {
        var $sidebar     = $element.children().first(),
            $content     = $element.children().last(),
            sidebarWidth = parseInt(getState(opts.widthStorageKey), 10) || opts.sidebarWidth;

        $element.addClass('sidebarized').css('position', 'relative');

        $sidebar = $sidebar.wrap($('<div>', { 'class': 'sidebar-content', 'height': '100%' })).parent().css('overflow', 'auto');
        $sidebar = $sidebar.wrap($('<div>', { 'class': 'sidebar' })).parent().css({
            'position': 'absolute',
            'height': '100%',
            'width': sidebarWidth
        });

        $content.addClass('content').css({
            'height': '100%',
            'margin-left': '0',
            'position': 'absolute',
            'left': sidebarWidth + opts.separatorWidth
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
            css: opts.separatorCss,
            height: '100%'
        }).css({ 'cursor': opts.resizeCursor, 'left': sidebarWidth });

        $separator.insertAfter($sidebar).on('dblclick', function () {
            if ($(this).hasClass('collapsed')) {
                expand($element, opts);
            } else {
                collapse($element, opts);
            }
        });

        $('<i>', { 'class': opts.collapseIcon, css: opts.iconCss }).on('click', function () {
            collapse($element, opts);
        }).appendTo($controls);

        $('<i>', { css: opts.iconCss }).on('click', function () {
            expand($element, opts);
        }).appendTo($separator).hide();

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
                var windowWidth = $(window).width(),
                    maxWidth    = opts.maxSidebarWidth || windowWidth - opts.separatorWidth,
                    position    = e.pageX;

                position = Math.min(Math.max(position, opts.minSidebarWidth), maxWidth);

                $('>.separator', $element).css('left', position);
                $('>.sidebar', $element).css('left', 0).width(position);
                $('>.content', $element).css('left', position + opts.separatorWidth);
            }

            function endSplit() {
                $(document).off('mousemove', doSplit).off('mouseup', endSplit);

                $element.children().css('-webkit-user-select', 'text');
                saveState(opts.widthStorageKey, parseInt($('>.sidebar', $element).width(), 10));
            }

            function startSplit() {
                if ($('>.separator', $element).hasClass('collapsed')) {
                    return;
                }
                $element.children().css('-webkit-user-select', 'none');

                $(document).on('mousemove', doSplit).on('mouseup', endSplit);
            }

            $('>.separator', $element).on('mousedown', startSplit);

            if (parseInt(getState(opts.stateStorageKey), 10) === 0) {
                collapse($element, opts);
            }

            $(window).on('resize', function () {
                adjustHeight($element);
            });
            $(document).ajaxSuccess(function () {
                adjustHeight($element);
            });

            $(window).trigger('resize');
        });
    };

    $.fn.sidebarize.defaults = {
        sidebarWidth: 250,
        minSidebarWidth: 200,
        maxSidebarWidth: null,
        widthStorageKey: 'sidebar_width',
        stateStorageKey: 'sidebar_state',
        separatorWidth: 9,
        collapsedSeparatorWidth: 22,
        controlsHeight: 25,
        collapseIcon: 'icon-double-angle-left',
        expandIcon: 'icon-double-angle-right',
        resizeCursor: 'e-resize',
        controlsCss: {
            'border': '1px solid #ddd',
            'text-align': 'right'
        },
        separatorCss: {
            'z-index': '100',
            'position': 'absolute',
            'user-select': 'none',
            '-webkit-user-select': 'none',
            '-khtml-user-select': 'none',
            '-moz-user-select': 'none',
            'width': '7px',
            'border': '1px solid #ddd'
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

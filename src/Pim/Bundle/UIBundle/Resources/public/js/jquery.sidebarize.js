/**
 * jQuery hideable sidebar plugin
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
(function ($) {
    'use strict';

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
        $('>.content', $element).css('left', opts.collapsedSeparatorWidth).width($(window).width() - opts.collapsedSeparatorWidth);
        $element.find('.separator i.' + opts.expandIcon).show();
    }

    function expand($element, opts) {
        $('>.sidebar', $element).show();
        var sidebarWidth = $('>.sidebar', $element).width();
        $('>.separator', $element).toggleClass('expanded collapsed').outerWidth(opts.separatorWidth).css({
            'left': sidebarWidth,
            'cursor': opts.resizeCursor
        });
        $('>.content', $element).css('left', sidebarWidth + opts.separatorWidth).width($(window).width() - opts.separatorWidth - sidebarWidth);
        $element.find('.separator i.' + opts.expandIcon).hide();
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
        var $sidebar = $element.children().first();
        var $content = $element.children().last();

        var sidebarWidth = parseInt($.cookie(opts.widthCookie), 10) || opts.sidebarWidth;

        $element.addClass('sidebarized').css('position', 'relative');

        $sidebar = $sidebar.wrap($('<div>', { 'class': 'sidebar-content', 'height': '100%' })).parent().css('overflow', 'auto');
        $sidebar = $sidebar.wrap($('<div>', { 'class': 'sidebar' })).parent().css({
            'position': 'absolute',
            'height': '100%',
            'width': sidebarWidth
        });

        $content.addClass('content').css({
            'height': '100%',
            'overflow-y': 'auto',
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

        $('<i>', { 'class': opts.expandIcon, css: opts.iconCss }).on('click', function () {
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

        if (!$.cookie) {
            throw new Error('Sidebarize: jQuery cookie plugin is required');
        }

        return this.each(function () {
            var $element = $(this);

            if ($element.hasClass('sidebarized')) {
                return;
            }
            if ($element.children().length !== 2) {
                throw new Error('Sidebarize: the element must have 2 child elements');
            }

            prepare($element, opts);

            function startSplit() {
                if ($('>.separator', $element).hasClass('collapsed')) {
                    return;
                }
                $element.children().css('-webkit-user-select', 'none');

                $(document).on('mousemove', doSplit).on('mouseup', endSplit);
            }

            function doSplit(e) {
                var windowWidth = $(window).width();
                var maxWidth = opts.maxSidebarWidth || windowWidth - opts.separatorWidth;
                var position = e.pageX;

                position = Math.min(Math.max(position, opts.minSidebarWidth), maxWidth);

                $('>.separator', $element).css('left', position);
                $('>.sidebar', $element).css('left', 0).width(position);
                $('>.content', $element).css('left', position + opts.separatorWidth).width(windowWidth - position - opts.separatorWidth);
            }

            function endSplit() {
                $(document).off('mousemove', doSplit).off('mouseup', endSplit);

                $element.children().css('-webkit-user-select', 'text');
            }

            $('>.separator', $element).on('mousedown', startSplit);

            if (opts.stateCookie) {
                if ($.cookie(opts.stateCookie) === 'collapsed') {
                    collapse($element, opts);
                }
            }

            if (opts.widthCookie) {
                $(window).on('unload', function () {
                    $.cookie(
                        opts.widthCookie,
                        parseInt($('>.sidebar', $element).width(), 10),
                        {
                            expires: opts.cookieExpiration || 365,
                            path: document.location.pathname
                        }
                    );
                });
            }

            if (opts.stateCookie) {
                $(window).on('unload', function () {
                    $.cookie(
                        opts.stateCookie,
                        $('>.separator', $element).hasClass('expanded') ? 'expanded' : 'collapsed',
                        {
                            expires: opts.cookieExpiration || 365,
                            path: document.location.pathname
                        }
                    );
                });
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
        sidebarWidth: 250,
        minSidebarWidth: 200,
        maxSidebarWidth: null,
        widthCookie: 'sidebar_width',
        stateCookie: 'sidebar_state',
        cookieExpiration: 365,
        separatorWidth: 9,
        collapsedSeparatorWidth: 22,
        controlsHeight: 25,
        collapseIcon: 'fa-icon-chevron-left',
        expandIcon: 'fa-icon-chevron-right',
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
            'font-size': 14,
            'line-height': '20px',
            'float': 'right',
            'margin': '0',
            'padding': '3px 6px 0',
            'cursor': 'pointer'
        },
        buttonsCss: {
            'float': 'left',
            'height': '25px',
            'line-height': '25px'
        },
        buttons: []
    };
})(jQuery);

/* ============================================================
 * jQuery hideable sidebar plugin
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

(function($) {
    "use strict";

    function getAvailableHeight($element, opts) {
        var height = $(window).height() - $element.offset().top - opts.heightCompensator;

        // @todo: remove in production environment
        if ($('.sf-toolbar').length && $('.sf-toolbar').height()) {
            height -= 39;
        }

        return height;
    }

    function collapse($element, opts) {
        $element.children().first().hide();
        $element.children().eq(1).toggleClass('expanded collapsed').outerWidth(opts.collapsedSeparatorWidth);
        $element.find('.sidebar-separator i.' + opts.expandIcon).show();
        $(window).trigger('resize');
    }

    function expand($element, opts) {
        $element.children().first().show();
        $element.children().eq(1).toggleClass('expanded collapsed').outerWidth(opts.separatorWidth);
        $element.find('.sidebar-separator i.' + opts.expandIcon).hide();
        $(window).trigger('resize');
    }

    function adjustHeight($element, opts) {
        var height = getAvailableHeight($element, opts);

        $element.outerHeight(height);
        $element.find('.sidebar-content').outerHeight(height - opts.controlsHeight);
    }

    function adjustWidth($element, opts) {
        var totalWidth = $(window).width();
        $element.outerWidth(totalWidth);

        var contentWidth;
        if ($element.children().first().is(':visible')) {
            var sidebarWidth = Math.floor(totalWidth * opts.sidebarPercentage/100);
            contentWidth = Math.floor(totalWidth - sidebarWidth - opts.separatorWidth);

            $element.children().first().outerWidth(sidebarWidth);
        } else {
            contentWidth = Math.floor(totalWidth - opts.collapsedSeparatorWidth);
        }

        $element.children().last().outerWidth(contentWidth);
    }

    function prepareControls($element, opts) {
        var $controls = $('<div>', { class: 'sidebar-controls', css: opts.controlsCss, height: opts.controlsHeight });

        var $collapseButton = $('<i>', { class: opts.collapseIcon, css: opts.iconCss }).on('click', function() {
            collapse($element, opts);
        }).appendTo($controls);

        var $sidebar = $element.children().first();
        if (opts.controlsPosition === 'top') {
            $sidebar.prepend($controls);
        } else {
            $sidebar.append($controls);
        }

        var $separator = $('<div>', { class: 'sidebar-separator expanded', css: opts.separatorCss, height: '100%' });
        $separator.insertAfter($sidebar).on('dblclick', function() {
            if ($(this).hasClass('collapsed')) {
                expand($element, opts);
            } else {
                collapse($element, opts);
            }
        });

        var $expandButton = $('<i>', { class: opts.expandIcon, css: opts.iconCss }).on('click', function() {
            expand($element, opts);
        }).appendTo($separator).hide();

        if (opts.controlsPosition === 'top') {
            $expandButton.css('top', 5);
        } else {
            $expandButton.css('bottom', 5);
        }

        for (var i in opts.buttons) {
            $(opts.buttons[i]).children('.dropdown-toggle').css(opts.buttonsCss);
            $(opts.buttons[i]).css(opts.buttonsCss).appendTo($controls);
        }
    }

    $.fn.sidebarize = function(options) {
        var opts = $.extend({}, $.fn.sidebarize.defaults, options);

        return this.each(function() {
            var $element = $(this);

            if ($element.hasClass('sidebarized')) {
                return;
            }

            var $children = $element.children();
            if ($children.length !== 2) {
                throw new Error('Sidebarize: the element must have 2 child elements');
            }
            var $sidebar = $children.first();
            var $content = $children.last();

            $element.addClass('sidebarized');
            $content.addClass('content pull-left');

            $sidebar = $sidebar.wrap($('<div>', { class: 'sidebar-content' })).parent().css('overflow', 'auto');
            $sidebar = $sidebar.wrap($('<div>', { class: 'sidebar pull-left' })).parent().height('100%');

            $content.css({ 'height': '100%', 'overflow-y': 'auto', 'margin-left': '0' });

            prepareControls($element, opts);

            $element.find('.sidebar-list li').on('click', function() {
                $element.find('.sidebar-list li').removeClass('active');
                $(this).addClass('active');
            });

            $(window).on('resize', function() {
                adjustHeight($element, opts);
                adjustWidth($element, opts);
            });
            $(document).ajaxSuccess(function() {
                adjustHeight($element, opts);
                adjustWidth($element, opts);
            });

            $(window).trigger('resize');
        });
    };

    $.fn.sidebarize.defaults = {
        sidebarPercentage: 15,
        controlsHeight: 25,
        controlsPosition: 'top',
        heightCompensator: 2,
        collapseIcon: 'fa-icon-chevron-left',
        expandIcon: 'fa-icon-chevron-right',
        controlsCss: {
            'border': '1px solid #ddd',
            'text-align': 'right'
        },
        iconCss: {
            'font-weight': 'bold',
            'font-size': 14,
            'line-height': '20px',
            'float': 'right',
            'margin': '3px 6px 0'
        },
        buttonsCss: {
            'float': 'left',
            'height': '25px'
        },
        separatorWidth: 9,
        collapsedSeparatorWidth: 22,
        separatorCss: {
            'position': 'relative',
            'float': 'left',
            'width': '7px',
            'border': '1px solid #ddd'
        },
        buttons: {}
    };

})(jQuery);

$(function () {
    "use strict";
    $('.has-sidebar').sidebarize();
});

/* ============================================================
 * jQuery hideable sidebar plugin
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

(function($) {
    "use strict";

    function triggerResize($element) {
        if (!$element) {
            $(window).trigger('resize');
        } else if ($element.outerWidth() < $(window).width()) {
            $(window).trigger('resize');
        }
    }

    function collapse($element, opts) {
        $element.children().first().hide();
        $element.children().eq(1).toggleClass('expanded collapsed');
        triggerResize();
    }

    function expand($element, opts) {
        $element.children().first().show();
        $element.children().eq(1).toggleClass('expanded collapsed');
        triggerResize();
        triggerResize($element);
    }

    function adjustHeight($element, opts) {
        var offset = $element.position().top - $('.scrollable-container').position().top;
        var height = $('.scrollable-container').height() - offset;
        height = height > opts.minHeight ? height : opts.minHeight;
        $element.outerHeight(height - 2);
    }

    function adjustWidth($element, opts) {
        var totalWidth = $element.outerWidth();

        if ($element.children().first().is(':visible')) {
            var sidebarWidth = Math.floor(totalWidth * opts.sidebarPercentage/100);
            var contentWidth = Math.floor(totalWidth - sidebarWidth - opts.separatorWidth);

            $element.children().first().outerWidth(sidebarWidth);
        } else {
            var contentWidth = Math.floor(totalWidth - opts.separatorWidth);
        }

        $element.children().last().outerWidth(contentWidth);
    }

    $.fn.sidebarize = function(options) {
        var opts = $.extend({}, $.fn.sidebarize.defaults, options);

        return this.each(function() {
            var $element = $(this);
            var $children = $element.children();
            if ($children.length !== 2) {
                throw new Error('Sidebarize: the element must have 2 child elements');
            }
            var $sidebar = $children.first();
            var $content = $children.last();

            $element.addClass(opts.elementClass);
            $content.addClass(opts.contentClass);

            $sidebar = $sidebar.wrap($('<div>').addClass(opts.sidebarClass)).parent();

            var $controls = $('<div>').addClass('sidebar-controls').css(opts.controlsCss);

            var $collapseButton = $('<i>').addClass(opts.collapseIcon).on('click', function() {
                collapse($element, opts);
            });

            var $expandButton = $('<i>').addClass(opts.expandIcon).on('click', function() {
                expand($element, opts);
            });

            $collapseButton.appendTo($controls);
            $controls.prependTo($sidebar);

            var $separator = $('<div>').addClass('sidebar-separator expanded').css(opts.separatorCss);
            $separator.height('100%').insertAfter($sidebar).on('click', function() {
                if ($(this).hasClass('collapsed')) {
                    expand($element, opts);
                }
            });

            $sidebar.height('100%').css('overflow-y', 'auto');
            $content.height('100%').css({ 'overflow-y': 'auto', 'margin-left': '0' });

            $(window).on('resize', function() {
                adjustHeight($element, opts);
                adjustWidth($element, opts);
            });
            $(document).ajaxSuccess(function() {
                adjustHeight($element, opts);
                adjustWidth($element, opts);
            });

            triggerResize();
        });
    }

    $.fn.sidebarize.defaults = {
        minHeight: 200,
        elementClass: 'row-fluid',
        sidebarClass: 'sidebar pull-left',
        contentClass: 'content pull-left',
        sidebarPercentage: 18,
        collapseIcon: 'icon-chevron-left',
        expandIcon: 'icon-chevron-right',
        controlsCss: {
            'border': '1px solid #ddd',
            'text-align': 'right'
        },
        separatorWidth: 9,
        separatorCss: {
            'float': 'left',
            'width': '7px',
            'border': '1px solid #ddd'
        }
    };

})(jQuery);


$(function () {
    $('.has-sidebar').sidebarize();
});

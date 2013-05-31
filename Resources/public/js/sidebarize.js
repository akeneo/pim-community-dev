/* ============================================================
 * jQuery hideable sidebar plugin
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

(function($) {
    "use strict";

    function triggerResize() {
        $(window).trigger('resize');
        setTimeout(function() {
            $('.scrollable-container').css('overflow', 'hidden');
        }, 50);
    }

    function collapse($element, opts) {
        $element.children().first().hide();
        $element.children().eq(1).toggleClass('expanded collapsed').outerWidth(opts.collapsedSeparatorWidth);
        $element.find('.sidebar-separator i.' + opts.expandIcon).show();
        triggerResize();
    }

    function expand($element, opts) {
        $element.children().first().show();
        $element.children().eq(1).toggleClass('expanded collapsed').outerWidth(opts.separatorWidth);
        $element.find('.sidebar-separator i.' + opts.expandIcon).hide();
        triggerResize();
    }

    function adjustHeight($element, opts) {
        var offset = $element.position().top - $('.scrollable-container').position().top;
        var height = $('.scrollable-container').height() - offset;

        $element.outerHeight(height - opts.heightCompensator);
        $element.find('.sidebar-content').outerHeight(height - opts.controlsHeight - opts.heightCompensator);
    }

    function adjustWidth($element, opts) {
        var totalWidth = $(window).width();
        $element.outerWidth(totalWidth);

        if ($element.children().first().is(':visible')) {
            var sidebarWidth = Math.floor(totalWidth * opts.sidebarPercentage/100);
            var contentWidth = Math.floor(totalWidth - sidebarWidth - opts.separatorWidth);

            $element.children().first().outerWidth(sidebarWidth);
        } else {
            var contentWidth = Math.floor(totalWidth - opts.collapsedSeparatorWidth);
        }

        $element.children().last().outerWidth(contentWidth);
    }

    function prepareControls($element, opts) {
        var $controls = $('<div>').addClass('sidebar-controls').css(opts.controlsCss).height(opts.controlsHeight);

        var $collapseButton = $('<i>').addClass(opts.collapseIcon).on('click', function() {
            collapse($element, opts);
        }).appendTo($controls);

        var $sidebar = $element.children().first();
        $sidebar.append($controls);

        var $separator = $('<div>').addClass('sidebar-separator expanded').css(opts.separatorCss);
        $separator.height('100%').insertAfter($sidebar).on('dblclick', function() {
            if ($(this).hasClass('collapsed')) {
                expand($element, opts);
            } else {
                collapse($element, opts);
            }
        });

        var $expandButton = $('<i>').addClass(opts.expandIcon).on('click', function() {
            expand($element, opts);
        }).css({ 'position': 'absolute', 'bottom': 10 }).appendTo($separator).hide();
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

            $sidebar = $sidebar.wrap($('<div>').addClass('sidebar-content')).parent().css('overflow', 'auto');
            $sidebar = $sidebar.wrap($('<div>').addClass('sidebar pull-left')).parent();

            $sidebar.height('100%');
            $content.height('100%').css({ 'overflow-y': 'auto', 'margin-left': '0' });

            prepareControls($element, opts);

            $(window).on('resize', function() {
                adjustHeight($element, opts);
                adjustWidth($element, opts);
            });
            $(document).ajaxSuccess(function() {
                adjustHeight($element, opts);
                adjustWidth($element, opts);
            });

            triggerResize();
            // Fix the issue with scrollable-container not having the right initial height
            setTimeout(triggerResize, 200);
        });
    }

    $.fn.sidebarize.defaults = {
        sidebarPercentage: 18,
        controlsHeight: 22,
        heightCompensator: 2,
        collapseIcon: 'icon-chevron-left',
        expandIcon: 'icon-chevron-right',
        controlsCss: {
            'border': '1px solid #ddd',
            'text-align': 'right'
        },
        separatorWidth: 9,
        collapsedSeparatorWidth: 22,
        separatorCss: {
            'position': 'relative',
            'float': 'left',
            'width': '7px',
            'border': '1px solid #ddd'
        }
    };

})(jQuery);

$(function () {
    $('.has-sidebar').sidebarize();
});

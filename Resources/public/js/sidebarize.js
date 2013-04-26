/* ============================================================
 * jQuery draggable/hideable sidebar plugin
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

(function($) {
    "use strict";
    $.fn.sidebarize = function() {
        if (!$(this).length) {
            return;
        }
        var element = $(this).selector;
        var sidebar = sidebar || '.resizable-sidebar';
        var content = content || '.resizable-content';
        $sidebar = $(element + ' ' + sidebar);
        $content = $(element + ' ' + content);
        var handle = $('<div class="resizable-sidebar-handle"></div>');
        var sidebarHeight = 355;
        var handleWidth = 7;
        this.init = function() {
            $sidebar.after(handle);
            $handle = $('.resizable-sidebar-handle');
            $handle.css({ 'float': 'left', 'width': handleWidth, 'height': sidebarHeight, 'cursor': 'col-resize' });
            $sidebar.css({ 'float': 'left', 'width': '180px', 'height': sidebarHeight });
            $content.css({ 'float': 'left', 'width': $(element).width() - $sidebar.width() - handleWidth });

            $('.resizable-sidebar-handle').mousedown(function(e) {
                var totalWidth = $(this).parent().width();
                $(document).mousemove(function(e) {
                    $('.resizable-sidebar').css("width", e.pageX);
                    $('.resizable-content').css("width", totalWidth - e.pageX - handleWidth);
                })
            });

            $(document).mouseup(function(e) {
                $(document).unbind('mousemove');
            });

            $('.resizable-sidebar-handle').dblclick(function() {
                $(this).prev().toggle();
            });

        };
        this.init();
    };

})(jQuery);


$(function () {
    $('.has-resizable-sidebar').sidebarize();
});

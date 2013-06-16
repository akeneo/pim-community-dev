/**
 * Plugin for creating pim multiselect popin
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */

(function ($) {
    "use strict";

    $.fn.pimMultiselect = function(options) {
        var opts = $.extend({}, $.fn.pimMultiselect.defaults, options);

        opts.selectedText = opts.title;
        opts.noneSelectedText = opts.title;

        return this.each(function() {
            $(this).addClass('pimmultiselect');

            if (!opts.appendTo) {
                opts.appendTo = $(this).parent();
            }
            opts.buttonPrependTo = opts.appendTo;

            $(this).multiselect(opts).multiselectfilter({
                label: false,
                placeholder: opts.placeholder
            });

            $('.ui-multiselect-menu.pimmultiselect').find('input[type="search"]').width(opts.searchBoxWidth);
        });
    }

    $.fn.pimMultiselect.defaults = {
        title: '',
        header: '',
        height: 175,
        minWidth: 225,
        searchBoxWidth: 207,
        classes: 'pimmultiselect',
        buttons: {},
        position: {
            my: 'right top',
            at: 'right bottom'
        },
        setButtonWidth: false,
        placeholder: 'Search'
    };

}(jQuery));

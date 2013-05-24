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

            if (opts.appendTo) {
            } else {
                opts.appendTo = $(this).parent();
            }
            opts.buttonPrependTo = opts.appendTo;

            $(this).multiselect(opts).multiselectfilter({
                label: false,
                placeholder: opts.placeholder
            });
        });
    }

    $.fn.pimMultiselect.defaults = {
        title: '',
        header: '',
        height: 175,
        minWidth: 225,
        classes: 'pimmultiselect',
        buttons: {},
        position: {
            my: 'left top',
            at: 'left bottom'
        },
        placeholder: 'Search'
    };

}(jQuery));

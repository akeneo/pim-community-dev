/**
 * Allow expanding/collapsing currency fields
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */

(function ($) {
    "use strict";

    function showTitle(el, opts) {
        var title = $(el).find('label').first().html();
        $(el).find('label').first().remove();
        var $title = $('<label>').addClass('control-label').html(title);
        $(el).prepend($title);
    }

    function getFields(el) {
        return $(el).find('>.control-group .control-group .control-group');
    }

    function prepareFields(el) {
        var $fields = [];

        getFields(el).each(function() {
            var $label = $(this).find('label').first();

            $fields.push(
                {'field': $(this), 'label': $label }
            );
        });
        return $fields;
    }

    function prepareLabels(el, opts) {

        var $fields = prepareFields(el);

        for (var i = 0; i < $fields.length; i++) {
            var $field = $fields[i].field;
            var $label = $fields[i].label;

            $label.addClass('add-on');

            var $controls = $field.find('.controls').first();
            $controls.addClass('input-prepend').prepend($label);
        }
    }

    function bindEvents(el, opts) {
        var $fields = getFields(el);
        $fields.first().off('click', 'label span');
        $fields.first().on('click', 'label span', function() {
            toggleOpen($(this).parents('.currencyfield'), opts);
        });

    }

    function expand(el, opts) {
        $(el).addClass('expanded').removeClass('collapsed');
        var $fields = getFields(el);

        $fields.find('label span').remove();
        var $icon = $('<span>').html($('<i>').addClass(opts.collapseIcon));
        $fields.first().find('label.control-label').prepend($icon);
        $fields.show();
    }

    function collapse(el, opts) {
        $(el).addClass('collapsed').removeClass('expanded');
        var $fields = getFields(el);
        $fields.hide();

        $fields.first().show();

        $fields.find('label span').remove();
        var $icon = $('<span>').html($('<i>').addClass(opts.expandIcon));
        getFields(el).first().find('label.control-label').prepend($icon);
    }

    function toggleOpen(el, opts, close) {
        var $fields = getFields(el);

        if ($fields.filter(':visible').length > 1 || close === true) {
            collapse(el, opts);
        } else {
            expand(el, opts);
        }
    }

    $.fn.currencyField = function(options) {
        var opts;
        if (typeof(options) === 'string' && options !== '') {
            opts = $.fn.currencyField.defaults;

            if (options === 'collapse') {
                return this.each(function() {
                    collapse(this, opts);
                });
            } else if (options === 'expand') {
                return this.each(function() {
                    expand(this, opts);
                });
            } else {
                return this;
            }
        } else {
            opts = $.extend({}, $.fn.currencyField.defaults, options);
        }

        return this.each(function() {
            if (!$(this).hasClass('currencyfield')) {
                $(this).addClass('currencyfield');
                showTitle(this, opts);
            }
            prepareLabels(this, opts);
            bindEvents(this, opts);
            toggleOpen(this, opts, true);
        });
    }

    $.fn.currencyField.defaults = {
        expandIcon: 'icon-caret-right',
        collapseIcon: 'icon-caret-down-gray'
    };

}(jQuery));

$(function() {
    "use strict";

    $('.currency').currencyField();
});

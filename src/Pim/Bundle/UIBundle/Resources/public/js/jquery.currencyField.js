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
        var $originalLabel = $(el).find('label').first();
        var $title = $('<label>').addClass($originalLabel.attr('class')).html($originalLabel.html());
        $originalLabel.remove();
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

            $label.addClass('add-on').children().remove();

            var $controls = $field.find('.controls').first();
            $controls.addClass('input-prepend').prepend($label);
        }
    }

    function bindEvents(el, opts) {
        getFields(el).first().off('click', 'label span').on('click', 'label span', function() {
            toggleOpen(el, opts);
        });
    }

    function prepareToggle(el, icon) {
        $(el).toggleClass('expanded collapsed');

        var $fields = getFields(el);

        $fields.find('label span').remove();
        var $icon = $('<span>').html($('<i>').addClass(icon));
        $fields.first().find('label.control-label').prepend($icon);
    }

    function expand(el, opts) {
        prepareToggle(el, opts.collapseIcon);

        getFields(el).show();
    }

    function collapse(el, opts) {
        if ($(el).find('.validation-error').length) {
            return;
        }
        prepareToggle(el, opts.expandIcon);

        getFields(el).hide().first().show();
    }

    function toggleOpen(el, opts) {
        if ($(el).hasClass('collapsed')) {
            expand(el, opts);
        } else {
            collapse(el, opts);
        }
    }

    $.fn.currencyField = function(options) {
        var opts;
        if (typeof(options) === 'string' && options !== '') {
            opts = $.fn.currencyField.defaults;

            if (options === 'collapse') {
                return this.each(function() {
                    if (getFields(this, opts).length > 1) {
                        collapse(this, opts);
                    }
                });
            } else if (options === 'expand') {
                return this.each(function() {
                    if (getFields(this, opts).length > 1) {
                        expand(this, opts);
                    }
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

            if (getFields(this, opts).length > 1) {
                bindEvents(this, opts);
                if (!$(this).hasClass('scopablefield')) {
                    toggleOpen(this, opts);
                } else {
                    collapse(this, opts);
                }
            }
        });
    }

    $.fn.currencyField.defaults = {
        expandIcon: 'fa-icon-caret-right',
        collapseIcon: 'fa-icon-caret-down'
    };

}(jQuery));

$(function() {
    "use strict";

    $('form div.currency').currencyField();
});

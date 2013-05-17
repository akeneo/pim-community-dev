/**
 * Allow expanding/collapsing scopable fields
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */

(function ($) {
    "use strict";

    function showTitle(el, opts) {
        var title = opts.title || $(el).data('field');
        var $title = $('<h5>').html(title);
        $(el).find('>h5').remove();
        $(el).prepend($title);
    }

    function getFields(el) {
        return $(el).find('>.control-group');
    }

    function prepareFields(el) {
        var $fields = [];
        $(el).find('>.control-group').each(function() {
            var $label = $(this).find('label').first();
            var scope = $(this).find('>:first-child').data('scope');

            $fields.push(
                {'field': $(this), 'label': $label, 'scope': scope }
            );
        });
        return $fields;
    }

    function sortFields(el, opts) {
        if (!opts.defaultScope) {
            return;
        }
        var $fields = prepareFields(el);

        for (var i = 0; i < $fields.length; i++) {
            var $field = $fields[i].field;
            var scope = $fields[i].scope;

            if (i !== 0 && scope === opts.defaultScope) {
                $field.insertBefore($fields[0].field);
                break;
            }
        }
    }

    function prepareLabels(el, opts) {
        var $fields = prepareFields(el);

        for (var i = 0; i < $fields.length; i++) {
            var $field = $fields[i].field;
            var $label = $fields[i].label;
            var scope = $fields[i].scope;

            $label.html(scope).addClass('add-on');
            $label.css(opts.labelCss);
            $label.height($field.height() - 10);

            var $controls = $field.find('.controls').first();
            $controls.addClass('input-prepend').prepend($label).css({'width': 'auto', 'max-width': '100%'});
        }
    }

    function bindEvents(el, opts) {
        var $fields = getFields(el);
        $fields.first().off('click', 'label span');
        $fields.first().on('click', 'label span', function() {
            toggleOpen($(this).parents('.scopablefield'), opts);
        });

    }

    function expand(el, opts) {
        var $fields = getFields(el);

        $fields.find('label span').remove();
        var $icon = $('<span>').html($('<i>').addClass(opts.collapseIcon)).css(opts.spanCss);
        $fields.first().find('label.control-label').prepend($icon);
        $fields.show();
    }

    function collapse(el, opts) {
        var $fields = getFields(el);
        $fields.hide();

        $fields.first().show();

        $fields.find('label span').remove();
        var $icon = $('<span>').html($('<i>').addClass(opts.expandIcon)).css(opts.spanCss);
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

    $.fn.scopableField = function(options) {
        var opts;
        if (typeof(options) === 'string' && options !== '') {
            opts = $.fn.scopableField.defaults;

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
            opts = $.extend({}, $.fn.scopableField.defaults, options);
        }

        $('.span4').removeClass('span4').addClass('input-large');
        this.find('.control-group').css('margin', 0);

        return this.each(function() {
            if (!$(this).hasClass('scopablefield')) {
                $(this).addClass('scopablefield');
                showTitle(this, opts);
            }
            sortFields(this, opts);
            prepareLabels(this, opts);
            bindEvents(this, opts);
            toggleOpen(this, opts, true);
        });
    }

    $.fn.scopableField.defaults = {
        defaultScope: null,
        title: null,
        expandIcon: 'icon-chevron-right',
        collapseIcon: 'icon-chevron-down',
        labelCss: {
            'padding-top': '4px',
            'width': '100px',
            'margin-bottom': 0,
            'border-radius': 0,
            'float': 'left'
        },
        spanCss: {
            'position': 'relative',
            'float': 'left',
            'left': '-5px',
            'top': '-4px',
            'padding': '4px 5px',
            'height': '100%'
        }
    };

}(jQuery));

$(function() {
    "use strict";

    $('form div.scopable').scopableField();
});

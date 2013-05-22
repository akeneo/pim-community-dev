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
        var title = opts.title || $(el).find('label').first().html();
        var $title = $('<label>').addClass('control-label').html(title);
        $(el).find('>label').remove();
        $(el).prepend($title);
    }

    function getFields(el) {
        return $(el).find('>.control-group');
    }

    function prepareFields(el) {
        var $fields = [];
        getFields(el).each(function() {
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

                if ($field.find('.wysihtml5-sandbox').length !== 0) {
                    var $el = $field.find('textarea').first();
                    destroyWysihtml5($el);
                    $el.wysihtml5();
                }

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

            if ($field.find('iframe.wysihtml5-sandbox').length > 0) {
                $field.find('iframe.wysihtml5-sandbox, textarea').width(opts.wysihtml5.width);
                $label.height(opts.wysihtml5.height);
            } else {
                $label.height($field.height() - 10);
            }

            var $controls = $field.find('.controls').first();
            $controls.addClass('input-prepend').prepend($label);
        }
    }

    function bindEvents(el, opts) {
        var $fields = getFields(el);
        $fields.first().off('click', 'label span');
        $fields.first().on('click', 'label span', function() {
            toggleOpen($(this).parents('.scopablefield'), opts, true);
        });

    }

    function expand(el, opts, force) {
        var $fields = getFields(el);

        $fields.find('label span').remove();
        var $icon = $('<span>').html($('<i>').addClass(opts.collapseIcon));
        $fields.first().find('label.control-label').prepend($icon);

        if (opts.toggleOnUpdate || force) {
            $fields.show();
        } else {
            $fields.hide();
            $fields.first().show();
        }
    }

    function collapse(el, opts, force) {
        var $fields = getFields(el);

        $fields.find('label span').remove();
        var $icon = $('<span>').html($('<i>').addClass(opts.expandIcon));
        $fields.first().find('label.control-label').prepend($icon);

        if (opts.toggleOnUpdate || force) {
            $fields.hide();
            $fields.first().show();
        } else {
            $fields.show();
        }
    }

    function toggleOpen(el, opts, force) {
        var $fields = getFields(el);

        if ($fields.filter(':visible').length === 1) {
            expand(el, opts, force);
        } else {
            collapse(el, opts, force);
        }
    }

    function destroyWysihtml5(el) {
        $(el).show().siblings('.wysihtml5-toolbar, .wysihtml5-sandbox, input[name="_wysihtml5_mode"]').remove();
    }

    $.fn.scopableField = function(options) {
        var opts;
        if (typeof(options) === 'string' && options !== '') {
            opts = $.fn.scopableField.defaults;

            if (options === 'collapse') {
                return this.each(function() {
                    collapse(this, opts, true);
                });
            } else if (options === 'expand') {
                return this.each(function() {
                    expand(this, opts, true);
                });
            } else {
                return this;
            }
        } else {
            opts = $.extend({}, $.fn.scopableField.defaults, options);
        }

        $('.span4').removeClass('span4').addClass('input-large');

        return this.each(function() {
            if (!$(this).hasClass('scopablefield')) {
                showTitle(this, opts);
            }
            sortFields(this, opts);
            prepareLabels(this, opts);
            bindEvents(this, opts);
            if (!$(this).hasClass('scopablefield') || opts.toggleOnUpdate === true) {
                toggleOpen(this, opts, true);
            } else {
                toggleOpen(this, opts);
            }
            $(this).addClass('scopablefield');
        });
    }

    $.fn.scopableField.defaults = {
        toggleOnUpdate: false,
        defaultScope: null,
        title: null,
        expandIcon: 'icon-caret-right',
        collapseIcon: 'icon-caret-down-gray',
        wysihtml5: {
            width: 523,
            height: 192
        }
    };

}(jQuery));

$(function() {
    "use strict";

    $('form div.scopable').scopableField();
});

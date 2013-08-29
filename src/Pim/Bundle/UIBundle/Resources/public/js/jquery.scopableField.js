/**
 * Allow expanding/collapsing scopable fields
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

(function ($) {
    'use strict';

    function showTitle(el, opts) {
        var $originalLabel = $(el).find('label').first();
        var title = opts.title || $originalLabel.html();
        var $title = $('<label>').addClass($originalLabel.attr('class')).html(title);
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

                break;
            }
        }
    }

    function prepareLabels(el) {
        var $fields = prepareFields(el);

        for (var i = 0; i < $fields.length; i++) {
            var $field = $fields[i].field;
            var $label = $fields[i].label;
            var scope = $fields[i].scope;

            $label.html(scope).addClass('add-on').height($field.children().first().actual('height') - 10);

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

    $.fn.scopableField = function(options) {
        var opts;
        if (typeof(options) === 'string' && options !== '') {
            opts = $.fn.scopableField.defaults;

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
            opts = $.extend({}, $.fn.scopableField.defaults, options);
        }

        return this.each(function() {
            var el = this;
            if (!$(el).hasClass('scopablefield')) {
                showTitle(el, opts);
            }

            if (getFields(el, opts).length < 2) {
                prepareLabels(el);
            } else {
                sortFields(el, opts);
                prepareLabels(el);
                bindEvents(el, opts);
                if (!$(el).hasClass('scopablefield') || opts.toggleOnUpdate === true) {
                    toggleOpen(el, opts);
                } else {
                    collapse(el, opts);
                }

                $(el).closest('form').on('validate', function() {
                    if ($(el).find('.validation-error:hidden').length) {
                        expand(el, opts);
                    }
                });
            }

            $(el).addClass('scopablefield');
        });
    };

    $.fn.scopableField.defaults = {
        toggleOnUpdate: false,
        defaultScope: null,
        title: null,
        expandIcon: 'fa-icon-caret-right',
        collapseIcon: 'fa-icon-caret-down'
    };

}(jQuery));

'use strict';
/**
 * Copy field extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'backbone',
        'underscore',
        'text!pim/template/product/tab/attribute/copy-field',
        'pim/i18n'
    ],
    function (Backbone, _, template, i18n) {
        return Backbone.View.extend({
            tagName: 'div',
            field: null,
            locale: null,
            scope: null,
            value: {},
            template: _.template(template),
            selected: false,
            events: {
                'click': 'select'
            },
            initialize: function () {
                this.selected = false;
                this.field    = null;
            },
            render: function () {
                this.$el.empty();

                var templateContext = {
                    type: this.field.attribute.field_type,
                    label: this.field.attribute.label[this.field.context.locale],
                    config: this.field.config,
                    attribute: this.field.attribute,
                    selected: this.selected,
                    locale: this.locale,
                    scope: this.scope,
                    i18n: i18n
                };

                this.$el.html(this.template(templateContext));
                this.field.renderCopyInput(this.value)
                    .then(_.bind(function (render) {
                        this.$('.field-input').html(render);
                    }, this));

                this.delegateEvents();

                return this;
            },
            setValue: function (value) {
                this.value = value;
            },
            setLocale: function (locale) {
                this.locale = locale;
            },
            setScope: function (scope) {
                this.scope = scope;
            },
            setField: function (field) {
                this.field = field;
            },
            select: function () {
                this.selected = !this.selected;

                this.field.render();
            },
            setSelected: function (selected) {
                this.selected = selected;
            }
        });
    }
);

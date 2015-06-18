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
            data: '',
            template: _.template(template),
            selected: false,
            events: {
                'change .copy-field-selector': 'selectionChanged',
                'click': 'select'
            },
            initialize: function () {
                this.selected = false;
                this.field    = null;
            },
            render: function () {
                this.$el.empty();

                var templateContext = {
                    type: this.field.fieldType,
                    label: this.field.attribute.label[this.field.context.locale],
                    data: this.data,
                    config: this.field.config,
                    context: this.field.context,
                    attribute: this.field.attribute,
                    selected: this.selected,
                    locale: this.locale,
                    scope: this.scope,
                    i18n: i18n
                };

                this.$el.html(this.template(templateContext));

                this.field.getTemplateContext().done(_.bind(function (templateContext) {
                    this.$('.field-input').html(this.field.renderCopyInput(templateContext, this.locale, this.scope));
                }, this));

                this.delegateEvents();

                return this;
            },
            setData: function (data) {
                this.data = data;
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
            selectionChanged: function (event) {
                this.selected = event.currentTarget.checked;
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

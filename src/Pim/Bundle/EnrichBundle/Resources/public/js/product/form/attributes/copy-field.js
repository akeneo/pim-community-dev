'use strict';

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
                'change .copy-field-selector': 'selectionChanged'
            },
            initialize: function()
            {
                this.selected = false;
                this.field    = null;

                return this;
            },
            render: function()
            {
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
                this.delegateEvents();

                return this;
            },
            getData: function()
            {
                if (this.editable && this.enabled) {
                    return this.model.get('values');
                } else {
                    return [];
                }
            },
            setData: function(data) {
                this.data = data;
            },
            setLocale: function(locale) {
                this.locale = locale;
            },
            setScope: function(scope) {
                this.scope = scope;
            },
            setField: function(field)
            {
                this.field = field;
            },
            selectionChanged: function(event) {
                this.selected = event.currentTarget.checked;
            },
            setSelected: function(selected) {
                this.selected = selected;
            }
        });
    }
);

"use strict";

define(['backbone', 'underscore', 'text!pim/template/product/tab/attribute/copy-field'], function (Backbone, _, copyFieldTemplate) {
    return Backbone.View.extend({
        tagName: 'div',
        attribute: null,
        field: 'text',
        context: {},
        config: {},
        template: _.template(copyFieldTemplate),
        initialize: function(attribute)
        {
            this.attribute    = attribute;
            this.context      = {};
            this.config       = {};

            return this;
        },
        render: function()
        {
            this.$el.empty();

            var templateContext = {
                type: this.field.fieldType,
                label: this.field.attribute.label[this.locale],
                data: this.data,
                config: this.field.config,
                context: this.field.context,
                attribute: this.attribute
            };

            this.$el.html(this.template(templateContext));
            this.delegateEvents();

            return this;
        },
        renderInput: function() {
            throw new Error('You should implement your field template');
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
        setChannel: function(channel) {
            this.channel = channel;
        },
        setField: function(field)
        {
            this.field = field;
        }
    });
});

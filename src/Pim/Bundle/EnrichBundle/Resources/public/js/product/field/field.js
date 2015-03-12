"use strict";

define(['backbone', 'underscore'], function (Backbone, _) {
    var config = {
        'locales': ['fr_FR', 'en_US'],
        'channels': ['ecommerce', 'mobile'],
        'currencies': ['EUR', 'USD']
    };

    var FieldModel = Backbone.Model.extend({});

    return Backbone.View.extend({
        tagName: 'div',
        attribute: null,
        context: {},
        model: FieldModel,
        template: function() { throw new Error('You should implement your field template'); },
        initialize: function(attribute)
        {
            this.attribute = attribute;
            this.model = new FieldModel();

            return this;
        },
        render: function()
        {
            this.$el.empty();
            var value = this.getCurrentValue();
            this.$el.html(this.template({label: this.attribute.label.en_US, value: value, config: config}));
            this.delegateEvents();

            return this;
        },
        getData: function()
        {
            return this.model.get('values');
        },
        setValues: function(values)
        {
            if (values.length === 0) {
                values.push(this.createEmptyValue());
            }

            this.model.set('values', values);
        },
        setContext: function(context)
        {
            this.context = context;
        },
        validate: function()
        {
            return true;
        },
        complete: function()
        {
            return true;
        },
        getCurrentValue: function()
        {
            var value = this.model.get('values').map(_.bind(function(currentValue) {
                if (this.attribute.localizable &&
                    this.attribute.scopable
                ) {
                    if (!(
                        currentValue.locale === this.context.locale &&
                        currentValue.scope === this.context.scope
                    )) {
                        return null;
                    }
                } else if (this.attribute.localizable) {
                    if (currentValue.locale !== this.context.locale) {
                        return null;
                    }
                } else if (this.attribute.scopable) {
                    if (currentValue.scope !== this.context.scope) {
                        return null;
                    }
                } else if (currentValue.scope || currentValue.locale) {
                    return null;
                }

                return currentValue;
            }, this)).filter(function(value) {
                return value !== null;
            })[0];

            if (!value) {
                value = this.createEmptyValue();
            }

            return value;
        },
        createEmptyValue: function() {
            return {
                value: null,
                locale: this.attribute.localizable ? this.context.locale : null,
                scope: this.attribute.scopable ? this.context.scope : null
            };
        },
        setCurrentValue: function(data)
        {
            var values = this.model.get('values');
            var value = this.getCurrentValue();

            if (null === value.value) {
                values.push(value);
            }

            value.value = data;
            this.model.set('values', values);
        }
    });
});

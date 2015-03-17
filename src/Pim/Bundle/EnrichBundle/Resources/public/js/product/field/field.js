"use strict";

define(['backbone', 'underscore', 'text!pim/template/product/field/field'], function (Backbone, _, fieldTemplate) {
    var FieldModel = Backbone.Model.extend({});

    return Backbone.View.extend({
        tagName: 'div',
        className: 'field-container',
        attribute: null,
        fieldType: 'text',
        context: {},
        config: {},
        model: FieldModel,
        template: _.template(fieldTemplate),
        infoElements: {},
        editable: true,
        enabled: true,
        initialize: function(attribute)
        {
            this.attribute    = attribute;
            this.model        = new FieldModel();
            this.infoElements = {};
            this.context      = {};
            this.config       = {};

            return this;
        },
        render: function()
        {
            this.$el.empty();
            var value = this.getCurrentValue();
            var templateContext = {
                type: this.fieldType,
                label: this.attribute.label[this.context.locale],
                value: value,
                config: this.config,
                context: this.context,
                attribute: this.attribute,
                info: this.infoElements,
                editMode: this.getEditMode()
            };

            this.$el.html(this.template(templateContext));
            this.$('.field-input').append(this.renderInput(templateContext));
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
        setConfig: function(config)
        {
            this.config = config;
        },
        addInfo: function(position, code, element) {
            if (!(position in this.infoElements)) {
                this.infoElements[position] = {};
            }
            this.infoElements[position][code] = element;

            this.render();
        },
        removeInfo: function(position, code) {
            delete this.infoElements[position][code];
        },
        validate: function()
        {
            return true;
        },
        complete: function()
        {
            return true;
        },
        setEditable: function(editable) {
            this.editable = editable;
        },
        getEditable: function() {
            return this.editable;
        },
        setEnabled: function(enabled) {
            this.enabled = enabled;
        },
        getEnabled: function() {
            return this.enabled;
        },
        getEditMode: function()
        {
            if (this.editable) {
                if (this.enabled) {
                    return 'edit';
                } else {
                    return 'disabled';
                }
            } else {
                return 'view';
            }
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
                value: this.getEmptyData(),
                locale: this.attribute.localizable ? this.context.locale : null,
                scope: this.attribute.scopable ? this.context.scope : null
            };
        },
        getEmptyData: function() {
            return null;
        },
        setCurrentValue: function(value)
        {
            var values = this.model.get('values');
            var productValue = this.getCurrentValue();

            productValue.value = value;
            this.model.set('values', values);
        }
    });
});

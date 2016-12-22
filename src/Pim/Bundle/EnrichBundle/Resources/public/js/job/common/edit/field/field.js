'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/common/property',
    'text!pim/template/export/common/edit/field/field'
], function (
    _,
    __,
    BaseForm,
    propertyAccessor,
    template
) {
    return BaseForm.extend({
        template: _.template(template),

        /**
         * {@inheritdoc}
         */
        initialize: function (config) {
            this.config = config.config;

            BaseForm.prototype.initialize.apply(this, arguments);
        },

        render: function () {
            this.$el.html(
                this.template(this.getTemplateContext())
            );

            this.$('.field-input').prepend(this.renderInput(this.getTemplateContext()));
            this.$('[data-toggle="tooltip"]').tooltip();

            this.delegateEvents();

            return this;
        },

        renderInput: function (templateContext) {
            return this.fieldTemplate(templateContext);
        },

        getTemplateContext: function () {
            return {
                __: __,
                value: this.getValue(),
                config: this.config,
                error: this.getParent().getValidationErrorsForField(this.getFieldCode())
            }
        },

        getValue: function () {
            return propertyAccessor.accessProperty(this.getFormData(), this.getFieldCode());
        },

        getFieldCode: function () {
            return this.config.fieldCode;
        },

        updateState: function () {
            var data = propertyAccessor.updateProperty(this.getFormData(), this.getFieldCode(), this.getFieldValue())

            this.setData(data);
        }
    });
});

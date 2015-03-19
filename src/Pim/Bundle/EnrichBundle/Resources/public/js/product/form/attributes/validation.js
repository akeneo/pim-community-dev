'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'oro/mediator',
        'pim/field-manager',
        'pim/product-edit-form/attributes/validation-error'
    ],
    function(_, Backbone, BaseForm, mediator, FieldManager, ValidationError) {
        return BaseForm.extend({
            initialize: function () {
                mediator.on('validation_error', _.bind(this.addValidationErrors, this));

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            addValidationErrors: function(data) {
                _.each(data.values, _.bind(function(fieldErrors, attributeCode) {
                    FieldManager.getField(attributeCode).done(_.bind(function (field) {
                        var validationError = new ValidationError(fieldErrors, this);

                        field.addElement(
                            'footer',
                            'validation',
                            validationError
                        );
                    }, this));

                }, this));
            },
            changeContext: function (locale, scope) {
                if (locale) {
                    this.getParent().setLocale(locale);
                }

                if (scope) {
                    this.getParent().setScope(scope);
                }
            }
        });
    }
);

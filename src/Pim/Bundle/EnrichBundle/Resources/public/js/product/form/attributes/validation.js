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
                mediator.on('validation_error', _.bind(this.validationError, this));

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            validationError: function(data) {
                this.removeValidationErrors();
                this.addValidationErrors(data);

                mediator.trigger('post_validation_error', _.bind(this.addValidationErrors, this));
            },
            removeValidationErrors: function() {
                var fields = FieldManager.getFields();

                _.each(fields, function(field) {
                    field.setValid(true);
                    field.removeElement('footer', 'validation');
                });
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

                        field.setValid(false);
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

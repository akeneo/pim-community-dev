'use strict';
/**
 * Validation extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'oro/mediator',
        'oro/messenger',
        'pim/field-manager',
        'pim/product-edit-form/attributes/validation-error',
        'pim/user-context'
    ],
    function ($, _, Backbone, BaseForm, mediator, messenger, FieldManager, ValidationError, UserContext) {
        return BaseForm.extend({
            validationErrors: {},

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', this.onPreSave);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.onValidationError);
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Pre save callback
             */
            onPreSave: function () {
                this.validationErrors = {};
            },

            /**
             * On validation callback
             *
             * @param {Event} event
             */
            onValidationError: function (event) {
                this.validationErrors = event.response;
                var globalErrors = _.where(this.validationErrors.values, {global: true});

                // Global errors with an empty property path
                _.each(globalErrors, function (error) {
                    messenger.notify('error', error.message);
                });

                this.getRoot().trigger('pim_enrich:form:entity:validation_error', event);
            },

            /**
             * On field extension
             *
             * @param {Event} event
             */
            addFieldExtension: function (event) {
                var field = event.field;
                var valuesErrors = _.uniq(this.validationErrors.values, function (error) {
                    return JSON.stringify(error);
                });

                var errorsForAttribute = _.where(valuesErrors, {attribute: field.attribute.code});

                if (!_.isEmpty(errorsForAttribute)) {
                    this.addErrorsToField(field, errorsForAttribute);
                }
            },

            /**
             * Add an error to a field
             *
             * @param {Object} field
             * @param {Array}  fieldError
             */
            addErrorsToField: function (field, fieldErrors) {
                field.addElement(
                    'footer',
                    'validation',
                    new ValidationError(fieldErrors, this)
                );

                field.setValid(false);
            },

            /**
             * Change the current context
             *
             * @param {[type]} locale
             * @param {[type]} scope
             */
            changeContext: function (locale, scope) {
                if (locale) {
                    UserContext.set('catalogLocale', locale);
                }

                if (scope) {
                    UserContext.set('catalogScope', scope);
                }
            }
        });
    }
);

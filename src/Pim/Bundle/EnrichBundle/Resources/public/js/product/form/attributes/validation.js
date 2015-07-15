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
            initialize: function () {
                this.stopListening(mediator, 'product:action:post_update');
                this.listenTo(mediator, 'product:action:post_update', this.onPostUpdate);

                this.stopListening(mediator, 'entity:action:validation_error');
                this.listenTo(mediator, 'entity:action:validation_error', this.onValidationError);

                this.stopListening(mediator, 'field:extension:add');
                this.listenTo(mediator, 'field:extension:add', this.addExtension);

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            onPostUpdate: function () {
                this.validationErrors = {};
            },
            onValidationError: function (event) {
                this.validationErrors = event.response;

                // Global errors with an empty property path
                if (this.validationErrors[''] && this.validationErrors[''].message) {
                    messenger.notificationFlashMessage('error', this.validationErrors[''].message);
                }
            },
            addExtension: function (event) {
                var field = event.field;
                var valuesErrors = this.validationErrors.values;

                if (valuesErrors && _.has(valuesErrors, field.attribute.code)) {
                    this.addErrorsToField(field, valuesErrors[field.attribute.code]);
                }
            },
            addErrorsToField: function (field, fieldErrors) {
                field.addElement(
                    'footer',
                    'validation',
                    new ValidationError(fieldErrors, this)
                );

                field.setValid(false);
            },
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

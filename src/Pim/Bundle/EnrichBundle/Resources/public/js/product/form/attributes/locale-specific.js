'use strict';
/**
 * Locale specific field extension
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/form',
        'oro/mediator'
    ],
    function ($, _, BaseForm, mediator) {
        return BaseForm.extend({
            configure: function () {
                this.listenTo(mediator, 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Add this field extension to the given field event
             *
             * @param {Object} event
             *
             * @returns {Promise}
             */
            addFieldExtension: function (event) {
                var field = event.field;

                if (!field.attribute.is_locale_specific) {
                    return;
                }

                if (!_.contains(field.attribute.locale_specific_codes, field.context.locale)) {
                    this.updateFieldElements(field);
                }

                return this;
            },

            /**
             * Update the given field by adding element to it
             *
             * @param {Object} field
             */
            updateFieldElements: function (field) {
                field.addElement(
                    'field-input',
                    'locale_specific',
                    _.__('pim_enrich.entity.product.locale_specific_attribute.unavailable')
                );
            }
        });
    }
);

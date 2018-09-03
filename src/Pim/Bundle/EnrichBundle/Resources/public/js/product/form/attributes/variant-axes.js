'use strict';
/**
 * This module sets variant axes as read only and add a message in the label of the field
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form'
    ],
    function (
        _,
        __,
        BaseForm
    ) {
        return BaseForm.extend({
            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            addFieldExtension: function (event) {
                const entity = this.getFormData();
                if (undefined === entity.meta || null === entity.meta.family_variant) {
                    return;
                }

                const axesAttributeCodes = entity.meta.attributes_axes;
                const field = event.field;

                if (axesAttributeCodes.includes(field.attribute.code)) {
                    field.setEditable(false);
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
                const message = '(' + __('pim_enrich.entity.product_model.module.variant_axis.label') + ')';
                const element = '<span class="">' + message + '</span>';

                field.addElement('label', 'variant_axis', element);
            }
        });
    }
);

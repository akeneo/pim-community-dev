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
                var productModel = this.getFormData();
                if (!productModel.meta.attributes_axes) {
                    return;
                }

                var axesAttributeCodes = productModel.meta.attributes_axes;
                var field = event.field;

                if (_.contains(axesAttributeCodes, field.attribute.code)) {
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
                var message = '(' + __('pim_enrich.entity.product_model.variant_axis') + ')';
                var element = '<span class="">' + message + '</span>';

                field.addElement('label', 'variant_axis', element);
            }
        });
    }
);

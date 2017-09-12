'use strict';
/**
 * This module sets parent attributes as read only and add a message in the footer of the field
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
                if (!productModel.meta.attributes_for_this_level) {
                    return;
                }

                var levelAttributeCodes = productModel.meta.attributes_for_this_level;
                var field = event.field;

                if (!_.contains(levelAttributeCodes, field.attribute.code)) {
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
                var productModel = this.getFormData();
                var message = '';

                if ('product_model' === productModel.meta.model_type) {
                    message = __('pim_enrich.entity.product_model.read_only_parent_attribute_from_common');
                } else {
                    // TODO: PIM-6451, specific message for variant products
                }

                var element = '<span class="AknFieldContainer-unavailable">' + message + '</span>';

                field.addElement('footer', 'read_only_parent_attribute', element);
            }
        });
    }
);

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
        'pim/user-context',
        'pim/form'
    ],
    function (
        _,
        __,
        UserContext,
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
                if (null === entity.meta.family_variant) {
                    return;
                }

                const levelAttributeCodes = entity.meta.attributes_for_this_level;
                const field = event.field;

                if (!levelAttributeCodes.includes(field.attribute.code)) {
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
                const entity = this.getFormData();
                const isProduct = ('product' === entity.meta.model_type);
                let message = __('pim_enrich.entity.product_model.read_only_parent_attribute_from_common');

                if (isProduct) {
                    const uiLocale = UserContext.get('uiLocale');
                    const comesFromParent = entity.meta.parent_attributes.includes(field.attribute.code);
                    const hasTwoLevelsOfVariation = (3 === entity.meta.variant_navigation.length);

                    if (comesFromParent && hasTwoLevelsOfVariation) {
                        const parentAxesLabels = entity.meta.variant_navigation[1].axes[uiLocale];
                        message = __(
                            'pim_enrich.entity.product_model.read_only_parent_attribute_from_model',
                            {axes: parentAxesLabels}
                        );
                    }
                }

                const element = '<span class="AknFieldContainer-unavailable">' + message + '</span>';

                field.addElement('footer', 'read_only_parent_attribute', element);
            }
        });
    }
);

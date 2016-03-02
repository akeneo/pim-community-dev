'use strict';

/**
 * Override of the attributes module.
 *
 * Purpose of this override is to avoid XHR call when removing an attribute
 * from the Product Edit Form (as we simply want to remove it from the DOM).
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/field-manager',
        'pim/security-context',
        'pim/product-edit-form/attributes'
    ],
    function (
        FieldManager,
        SecurityContext,
        BaseAttributes
    ) {
        return BaseAttributes.extend({
            /**
             * {@inheritdoc}
             */
            removeAttribute: function (event) {
                if (!SecurityContext.isGranted('pim_enrich_product_remove_attribute')) {
                    return;
                }
                var attributeCode = event.currentTarget.dataset.attribute;
                var product = this.getFormData();
                var fields = FieldManager.getFields();

                this.triggerExtensions('add-attribute:update:available-attributes');

                delete product.values[attributeCode];
                delete fields[attributeCode];

                this.setData(product);
                this.getRoot().trigger('pim_enrich:form:remove-attribute:after');

                this.render();
            }
        });
    }
);

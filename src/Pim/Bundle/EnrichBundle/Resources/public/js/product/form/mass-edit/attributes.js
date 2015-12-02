'use strict';
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

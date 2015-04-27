'use strict';

define(
    [
        'underscore',
        'pim/form',
        'pim/field-manager',
        'pim/entity-manager',
        'text!pim/template/product/tab/attribute/variant-group'
    ],
    function (_, BaseForm, FieldManager, EntityManager, variantGroupTemplate) {
        return BaseForm.extend({
            template: _.template(variantGroupTemplate),
            render: function () {
                var product = this.getData();
                if (!product.variant_group) {
                    return;
                }

                EntityManager.getRepository('variantGroup').find(product.variant_group)
                    .done(_.bind(function (variantGroup) {
                        var fields = FieldManager.getFields();

                        _.each(fields, _.bind(function (field) {
                            if (variantGroup.values && _.contains(_.keys(variantGroup.values), field.attribute.code)) {
                                var $element = this.template({
                                    variantGroup: variantGroup
                                });

                                field.setEnabled(false);
                                field.addElement('footer', 'updated_by', $element);
                            }
                        }, this));
                    }, this));

                return this;
            }
        });
    }
);

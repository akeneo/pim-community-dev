'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'pim/field-manager',
        'pim/variant-group-manager',
        'text!pim/template/product/tab/attribute/variant-group'
    ],
    function(_, Backbone, BaseForm, FieldManager, VariantGroupManager, variantGroupTemplate) {
        return BaseForm.extend({
            template: _.template(variantGroupTemplate),
            render: function() {
                var product = this.getData();
                if (!product.variant_group) {
                    return;
                }

                VariantGroupManager.getVariantGroup(product.variant_group).done(_.bind(function(variantGroup) {
                    var fields = FieldManager.getFields();

                    _.each(fields, _.bind(function(field) {
                        if (variantGroup.values && _.contains(_.keys(variantGroup.values), field.attribute.code)) {
                            var $element = this.template({
                                variantGroup: variantGroup
                            });

                            field.setEnabled(false);
                            field.addElement('footer', 'coming_from_variant_group', $element);
                        }
                    }, this));
                }, this));

                return this;
            }
        });
    }
);

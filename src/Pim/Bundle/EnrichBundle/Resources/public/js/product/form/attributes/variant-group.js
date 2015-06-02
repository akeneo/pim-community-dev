'use strict';

define(
    [
        'underscore',
        'pim/form',
        'pim/field-manager',
        'pim/entity-manager',
        'oro/mediator',
        'text!pim/template/product/tab/attribute/variant-group'
    ],
    function (_, BaseForm, FieldManager, EntityManager, mediator, variantGroupTemplate) {
        return BaseForm.extend({
            template: _.template(variantGroupTemplate),
            configure: function () {
                mediator.off(null, null, 'context:product:form:attribute:variant-group');
                mediator.on(
                    'field:extension:add',
                    _.bind(this.addExtension, this),
                    'context:product:form:attribute:variant-group'
                );

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            addExtension: function (event) {
                var product = this.getData();
                if (!product.variant_group) {
                    return;
                }

                EntityManager.getRepository('variantGroup').find(product.variant_group)
                    .done(_.bind(function (variantGroup) {
                        var field = event.field;
                        if (variantGroup.values && _.contains(_.keys(variantGroup.values), field.attribute.code)) {
                            var $element = this.template({
                                variantGroup: variantGroup
                            });

                            field.setEnabled(false);
                            field.addElement('footer', 'updated_by', $element);
                        }
                    }, this));

                return this;
            }
        });
    }
);

'use strict';
/**
 * Variant group extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/form',
        'pim/field-manager',
        'pim/fetcher-registry',
        'oro/mediator',
        'pim/template/product/tab/attribute/variant-group'
    ],
    function ($, _, BaseForm, FieldManager, FetcherRegistry, mediator, variantGroupTemplate) {
        return BaseForm.extend({
            template: _.template(variantGroupTemplate),
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            addFieldExtension: function (event) {
                var product = this.getFormData();
                if (!product.variant_group) {
                    return;
                }

                event.promises.push(
                    FetcherRegistry.getFetcher('variant-group').fetch(product.variant_group, {cached: true})
                        .then(function (variantGroup) {
                            var field = event.field;
                            if (variantGroup.values && _.contains(_.keys(variantGroup.values), field.attribute.code)) {
                                var $element = this.template({
                                    variantGroup: variantGroup
                                });

                                field.setEditable(false);
                                field.addElement('footer', 'updated_by', $element);
                            }
                        }.bind(this))
                );

                return this;
            }
        });
    }
);

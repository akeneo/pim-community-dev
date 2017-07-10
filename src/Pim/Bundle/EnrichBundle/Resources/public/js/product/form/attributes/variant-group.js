
/**
 * Variant group extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery';
import _ from 'underscore';
import BaseForm from 'pim/form';
import FieldManager from 'pim/field-manager';
import FetcherRegistry from 'pim/fetcher-registry';
import mediator from 'oro/mediator';
import variantGroupTemplate from 'pim/template/product/tab/attribute/variant-group';
export default BaseForm.extend({
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


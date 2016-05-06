'use strict';

/**
 * Variant group meta extension to display number of products this group contains
 *
 * @author    Adrien Petremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'text!pim/template/variant-group/meta/product-count'
    ],
    function (_, __, BaseForm, formTemplate) {
        return BaseForm.extend({
            tagName: 'span',
            template: _.template(formTemplate),

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var variantGroup = this.getFormData();
                var html = '';

                if (_.has(variantGroup, 'products')) {
                    html = this.template({
                        label: __('pim_enrich.entity.variant_group.meta.product_count'),
                        productCount: variantGroup.products.length
                    });
                }

                this.$el.html(html);

                return this;
            }
        });
    }
);

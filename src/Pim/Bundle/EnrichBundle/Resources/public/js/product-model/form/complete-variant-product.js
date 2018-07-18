/**
 * On a Product Model Edit Form, this module displays number of product variant in the subtree of this Product Model,
 * eg: 2 / 10.
 *
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/product-model/complete-variant-product',
        'pim/user-context'
    ],
    function (
        _,
        __,
        BaseForm,
        template,
        UserContext
    ) {
        return BaseForm.extend({
            className: 'AknButtonList-item',
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            configure: function () {
                UserContext.off('change:catalogLocale change:catalogScope', this.render);

                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                const variantProductCompleteness = this.getFormData().meta.variant_product_completenesses;
                const completenesses = variantProductCompleteness.completenesses;
                const channel = UserContext.get('catalogScope');
                const locale = UserContext.get('catalogLocale');

                const totalProducts = variantProductCompleteness.total;
                let completeProducts = 0;

                if (_.has(completenesses, channel) &&
                    _.has(completenesses[channel], locale)
                ) {
                    completeProducts = completenesses[channel][locale];
                }

                this.$el.html(
                    this.template({
                        color: this.badgeCssClass(completeProducts, totalProducts),
                        label: this.badgeLabel(completeProducts, totalProducts)
                    })
                );
            },

            /**
             * Return the color of the badge
             *
             * @param {int} completeProducts
             * @param {int} totalProducts
             *
             * @returns {string}
             */
            badgeCssClass: function (completeProducts, totalProducts) {
                const ratio = completeProducts / totalProducts;
                let color = 'warning';

                if (1 === ratio) {
                    color = 'success';
                } else if (0 === ratio || 0 === totalProducts) {
                    color = 'important';
                }

                return color;
            },

            /**
             * Return the label of the badge
             *
             * @param {int} completeProducts
             * @param {int} totalProducts
             *
             * @returns {string}
             */
            badgeLabel: function (completeProducts, totalProducts) {
                return __(
                    'pim_enrich.entity.product_model.module.completeness.variant_product',
                    { complete: completeProducts, total: totalProducts },
                    completeProducts
                );
            }
        });
    }
);

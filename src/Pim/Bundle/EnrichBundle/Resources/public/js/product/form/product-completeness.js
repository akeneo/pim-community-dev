'use strict';
/**
 * Product completeness extension
 * Displays the global completeness of the product.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/user-context',
        'pim/template/product/form/product-completeness'
    ],
    function (
        _,
        __,
        BaseForm,
        UserContext,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            render: function () {
                this.$el.empty();

                this.$el.append(this.template({
                    label: __('pim_enrich.entity.product.completeness'),
                    completeness: this.getRatio()
                }));

                return this;
            },

            /**
             * Returns the ratio of the current locale and current scope
             *
             * @returns number
             */
            getRatio: function () {
                var completeness = _.findWhere(
                    this.getFormData().meta.completenesses,
                    { locale: UserContext.get('catalogLocale') }
                );

                return completeness.channels[UserContext.get('catalogScope')].completeness.ratio;
            }
        });
    }
);

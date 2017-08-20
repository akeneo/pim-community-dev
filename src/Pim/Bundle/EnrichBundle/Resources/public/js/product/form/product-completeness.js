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

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', this.render.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:scope_switcher:change', this.render.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritDoc}
             */
            render: function () {
                this.$el.empty();

                var ratio = this.getRatio();
                if (null !== ratio) {
                    this.$el.append(this.template({
                        label: __('pim_enrich.entity.product.completeness'),
                        completeness: ratio,
                        badgeClass: this.getBadgeClass()
                    }));
                }

                return this;
            },

            /**
             * Returns the ratio of the current locale and current scope
             *
             * @returns number|null
             */
            getRatio: function () {
                var completeness = _.findWhere(
                    this.getFormData().meta.completenesses,
                    { channel: UserContext.get('catalogScope') }
                );

                if (undefined === completeness) {
                    return null;
                }

                completeness = completeness.locales[UserContext.get('catalogLocale')];
                if (undefined === completeness) {
                    return null;
                }

                return Math.round(completeness.completeness.ratio);
            },

            /**
             * Returns the HTML class for the badge from the completeness ratio
             *
             * @returns string
             */
            getBadgeClass: function() {
                var ratio = this.getRatio();

                if (ratio <= 0) {
                    return 'AknBadge--important';
                }

                if (ratio >= 100) {
                    return 'AknBadge--enabled';
                }

                return 'AknBadge--warning';
            }
        });
    }
);

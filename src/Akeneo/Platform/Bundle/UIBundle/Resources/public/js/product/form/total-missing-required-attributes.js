'use strict';
/**
 * Displays the total missing required attributes
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/router',
        'pim/form',
        'pim/user-context',
        'pim/provider/to-fill-field-provider',
        'pim/template/product/form/total-missing-required-attributes'
    ],
    function (
        _,
        __,
        router,
        BaseForm,
        UserContext,
        toFillFieldProvider,
        template
    ) {
        return BaseForm.extend({
            className: 'AknButtonList-item',
            template: _.template(template),
            events: {
                'click .required-attribute-indicator': 'filterRequiredAttributes'
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritDoc}
             */
            render: function () {
                this.$el.empty();

                const product = this.getFormData();
                const scope = UserContext.get('catalogScope');
                const locale = UserContext.get('catalogLocale');

                const missingAttributes = toFillFieldProvider.getMissingRequiredFields(product, scope, locale);

                if (missingAttributes.length > 0) {
                    this.$el.append(this.template({
                        __: __,
                        missingRequiredAttributesCount: missingAttributes.length,
                        missingValues: 'pim_enrich.entity.product.module.completeness.missing_values'
                    }));
                }

                return this;
            },

            /**
             * Filter the required attributes and attribute group
             */
            filterRequiredAttributes: function () {
                this.getRoot().trigger('pim_enrich:form:switch_values_filter', 'missing_required');
            }
        });
    }
);

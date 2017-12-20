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
        'pim/template/product/form/total-missing-required-attributes'
    ],
    function (
        _,
        __,
        router,
        BaseForm,
        UserContext,
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
                const missingAttributesCount = this.getMissingRequiredAttributesCount(
                    UserContext.get('catalogScope'),
                    UserContext.get('catalogLocale')
                );

                if (missingAttributesCount > 0) {
                    this.$el.append(this.template({
                        __: __,
                        missingRequiredAttributesCount: missingAttributesCount,
                        missingValues: 'pim_enrich.form.product.panel.completeness.missing_values'
                    }));
                }

                return this;
            },

            /**
             * Filter the required attributes and attribute group
             */
            filterRequiredAttributes: function () {
                this.getRoot().trigger('pim_enrich:form:switch_values_filter', 'missing_required');
            },

            /**
             * Returns the missing required attributes count of the current scope and locale
             *
             * @param scope String
             * @param locale String
             *
             * @return Object
             */
            getMissingRequiredAttributesCount: function (scope, locale) {
                const scopeCompleteness =  _.findWhere(this.getFormData().meta.completenesses, {channel: scope});
                if (undefined === scopeCompleteness) {
                    return 0;
                }

                const localeCompleteness = scopeCompleteness.locales[locale];
                if (undefined === localeCompleteness) {
                    return 0;
                }

                const product = this.getFormData();

                if ('product' === product.meta.model_type) {
                    return localeCompleteness.completeness.missing;
                }

                const missingAttributeCodes = localeCompleteness.missing.map(missing => missing.code);
                const levelAttributeCodes = Object.keys(product.values);

                const missingLevelAttributes = missingAttributeCodes.filter(missingAttribute =>
                    levelAttributeCodes.includes(missingAttribute)
                );

                return missingLevelAttributes.length;
            }
        });
    }
);

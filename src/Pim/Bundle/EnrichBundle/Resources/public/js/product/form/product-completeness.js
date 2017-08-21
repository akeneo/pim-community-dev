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
            className: 'AknDropdown',
            template: _.template(template),
            events: {
                'click .missing-attribute': 'showAttribute'
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', this.render.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:scope_switcher:change', this.render.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', function (localeEvent) {
                    if ('base_product' === localeEvent.context) {
                        this.render(localeEvent.localeCode);
                    }
                }.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritDoc}
             *
             * @param localeCode String
             */
            render: function (localeCode) {
                this.$el.empty();

                var ratio = this.getCurrentRatio();
                if (null !== ratio) {
                    this.$el.append(this.template({
                        __: __,
                        label: __('pim_enrich.entity.product.completeness'),
                        ratio: ratio,
                        completenesses: this.getCurrentCompletenesses(),
                        badgeClass: this.getBadgeClass(),
                        currentLocale: undefined !== localeCode ? localeCode : UserContext.get('catalogLocale'),
                        missingValues: 'pim_enrich.form.product.panel.completeness.missing_values'
                    }));
                }

                return this;
            },

            /**
             * Returns the completeness of the current scope
             */
            getCurrentCompletenesses: function () {
                return _.findWhere(
                    this.getFormData().meta.completenesses,
                    { channel: UserContext.get('catalogScope') }
                );
            },

            /**
             * Returns the ratio of the current scope and current locale
             *
             * @returns number|null
             */
            getCurrentRatio: function () {
                var completenesses = this.getCurrentCompletenesses();
                if (undefined === completenesses) {
                    return null;
                }

                var completeness = completenesses.locales[UserContext.get('catalogLocale')];
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
                var ratio = this.getCurrentRatio();
                if (ratio <= 0) {
                    return 'AknBadge--important';
                }

                if (ratio >= 100) {
                    return 'AknBadge--enabled';
                }

                return 'AknBadge--warning';
            },

            /**
             * Set focus to the attribute given by the event
             *
             * @param event Event
             */
            showAttribute: function (event) {
                this.getRoot().trigger(
                    'pim_enrich:form:locale_switcher:change',
                    {
                        localeCode: event.currentTarget.dataset.locale,
                        context: 'base_product'
                    }
                );
                this.getRoot().trigger(
                    'pim_enrich:form:show_attribute',
                    {
                        attribute: event.currentTarget.dataset.attribute,
                        locale: event.currentTarget.dataset.locale,
                        scope: UserContext.get('catalogScope')
                    }
                );
                this.render();
            }
        });
    }
);

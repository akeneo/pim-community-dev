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
                this.listenTo(this.getRoot(), 'pim_enrich:form:scope_switcher:change', function (scopeEvent) {
                    if ('base_product' === scopeEvent.context) {
                        this.render({ scope: scopeEvent.scopeCode });
                    }
                }.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', function (localeEvent) {
                    if ('base_product' === localeEvent.context) {
                        this.render({ locale: localeEvent.localeCode});
                    }
                }.bind(this));

                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritDoc}
             *
             * @param options Object
             * @param options.locale String
             * @param options.scope  String
             */
            render: function (options) {
                options = _.extend({
                    locale: UserContext.get('catalogLocale'),
                    scope: UserContext.get('catalogScope')
                }, options);
                this.$el.empty();

                const ratio = this.getCurrentRatio(options);
                if (null !== ratio) {
                    this.$el.append(this.template({
                        __: __,
                        label: __('pim_enrich.entity.product.completeness'),
                        ratio: ratio,
                        completenesses: this.getCurrentCompletenesses(options.scope),
                        badgeClass: this.getBadgeClass(options),
                        currentLocale: options.locale,
                        missingValues: 'pim_enrich.form.product.panel.completeness.missing_values'
                    }));
                }

                return this;
            },

            /**
             * Returns the completeness of the current scope
             *
             * @param scope String
             *
             * @return Object
             */
            getCurrentCompletenesses: function (scope) {
                return _.findWhere(this.getFormData().meta.completenesses, {channel: scope});
            },

            /**
             * Returns the ratio of the current scope and current locale
             *
             * @param options Object
             * @param options.locale String
             * @param options.scope  String
             *
             * @returns number|null
             */
            getCurrentRatio: function (options) {
                const completenesses = this.getCurrentCompletenesses(options.scope);
                if (undefined === completenesses) {
                    return null;
                }

                const completeness = completenesses.locales[options.locale];
                if (undefined === completeness) {
                    return null;
                }

                return Math.round(completeness.completeness.ratio);
            },

            /**
             * Returns the HTML class for the badge from the completeness ratio
             *
             * @param options Object
             * @param options.locale String
             * @param options.scope  String
             *
             * @returns string
             */
            getBadgeClass: function(options) {
                const ratio = this.getCurrentRatio(options);
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

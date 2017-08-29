'use strict';

/**
 * Extension to render a list of activated locales used for the product grid.
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'jquery',
        'underscore',
        'pim/form',
        'pim/template/product/grid/locale-switcher',
        'pim/fetcher-registry',
        'pim/i18n',
        'pim/router',
        'pim/user-context'
    ],
    function (
        $,
        _,
        BaseForm,
        template,
        FetcherRegistry,
        i18n,
        router,
        UserContext
    ) {
        return BaseForm.extend({
            template: _.template(template),
            config: {},
            locales: [],

            events: {
                'click [data-locale]': 'changeLocale'
            },

            /**
             * {@inheritdoc}
             */
            initialize(config) {
                this.config = config.config;

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
            */
            configure() {
                this.fetchLocales().then(locales => this.locales = locales);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                const currentLocaleCode = UserContext.get('catalogLocale');

                this.$el.empty().append(this.template({
                    locales: this.locales,
                    currentLocaleCode,
                    i18n,
                    getDisplayName: this.getDisplayName
                }));
            },

            /**
             * Fetch the activated locales to render in the list
             * @return {Array} An array of activated locales
             */
            fetchLocales() {
                return FetcherRegistry.getFetcher('locale').search({
                    activated: true,
                    cached: false
                });
            },

            /**
             * Transforms the locale code
             * @param  {String} localeCode The original localeCode like fr_FR
             * @return {String}            The shortened localeCode like fr
             */
            getDisplayName(localeCode) {
                return localeCode.split('_')[0];
            },

            /**
             * Switches locales by visiting the product grid route
             * @param  {Event} event The click event coming from the locale dropdown list
             */
            changeLocale(event) {
                const { localeParamName } = this.config;
                const localeCode = this.$(event.currentTarget).attr('data-locale');
                router.redirectToRoute(this.config.routeName, { [localeParamName]: localeCode });
            }
        });
    });

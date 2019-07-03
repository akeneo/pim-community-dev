'use strict';

/**
 * Extension to render a list of activated locales used for the product grid.
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/product/grid/locale-switcher',
        'pim/fetcher-registry',
        'pim/i18n',
        'pim/router',
        'pim/user-context'
    ], function (
        $,
        _,
        __,
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
            id: 'locale-switcher',
            className: 'AknDropdown AknColumn-block locale-switcher',
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
                return $.when(
                    this.fetchLocales().then(locales => {
                        this.locales = locales;
                        const currentLocaleCode = UserContext.get('catalogLocale');
                        let currentLocale = _.find(this.locales, {code: currentLocaleCode});
                        if (undefined === currentLocale) {
                            currentLocale = _.first(this.locales);
                            UserContext.set('catalogLocale', currentLocale.code);
                        }
                    }),
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },

            /**
             * {@inheritdoc}
             */
            render() {
                const currentLocaleCode = UserContext.get('catalogLocale');
                let currentLocale = _.find(this.locales, { code: currentLocaleCode });

                this.$el.empty().append(this.template({
                    localeLabel: __('pim_enrich.entity.locale.uppercase_label'),
                    locales: this.locales,
                    currentLocale,
                    i18n,
                    getDisplayName: this.getDisplayName
                }));
            },

            /**
             * Fetch the activated locales to render in the list
             * @return {Array} An array of activated locales
             */
            fetchLocales() {
                const localeFetcher = FetcherRegistry.getFetcher('locale');

                return localeFetcher.fetchActivated();
            },

            /**
             * Returns the string to display for a locale
             *
             * @param  {Object} locale The original locale
             * @return {String}        The translated locale
             */
            getDisplayName(locale) {
                return locale.language;
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

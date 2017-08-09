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
        'routing'
    ],
    function (
        $,
        _,
        BaseForm,
        template,
        FetcherRegistry,
        i18n,
        Routing
    ) {
        return BaseForm.extend({
            template: _.template(template),
            config: {},

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
            render() {
                this.fetchLocales().then((locales) => {
                    const currentLocaleCode = this.getCurrentLocale() || _.first(locales).code;

                    this.$el.empty().append(this.template({
                        locales,
                        currentLocaleCode,
                        i18n,
                        getDisplayName: this.getDisplayName,
                        generateUrl: this.generateUrl.bind(this)
                    }));
                });
            },

            /**
             * Fetch the activated locales to render in the list
             * @return {Array} An array of activated locales
             */
            fetchLocales() {
                return FetcherRegistry.getFetcher('locale').fetchActivated();
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
             * Generates a product grid url with the locale code
             * @param  {String} localeCode The locale code - e.g. en_US
             * @return {String}        A url like #/enrich/product/?dataLocale=en_US
             */
            generateUrl(localeCode) {
                const { localeParamName } = this.config;

                return Routing.generate(this.config.routeName, { [localeParamName]: localeCode });
            },

            /**
             * @TODO - Get this information elsewhere
             * @return {String} Returns a string with the locale code e.g. en_US
             */
            getCurrentLocale() {
                return window.location.hash.split(`?${this.config.localeParamName}=`)[1];
            }
        });
    });

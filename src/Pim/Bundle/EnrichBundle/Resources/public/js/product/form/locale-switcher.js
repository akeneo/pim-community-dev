'use strict';
/**
 * Locale switcher extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form',
        'pim/template/product/locale-switcher',
        'pim/fetcher-registry',
        'pim/i18n'
    ],
    function (_, BaseForm, template, FetcherRegistry, i18n) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknDropdown AknButtonList-item locale-switcher',
            events: {
                'click li a': 'changeLocale'
            },
            render: function () {
                this.getDisplayedLocales()
                    .done(function (locales) {
                        var params = { localeCode: _.first(locales).code };
                        this.trigger('pim_enrich:form:locale_switcher:pre_render', params);

                        this.$el.html(
                            this.template({
                                locales: locales,
                                currentLocale: _.findWhere(locales, {code: params.localeCode}),
                                i18n: i18n
                            })
                        );
                        this.delegateEvents();
                    }.bind(this));

                return this;
            },

            /**
             * Retrieve locales to display in the locale switcher
             *
             * @returns {Promise}
             */
            getDisplayedLocales: function () {
                return FetcherRegistry.getFetcher('locale').fetchActivated();
            },

            /**
             * Method triggered on the 'change locale' event
             *
             * @param {Object} event
             */
            changeLocale: function (event) {
                this.trigger('pim_enrich:form:locale_switcher:change', {
                    localeCode: event.currentTarget.dataset.locale
                });

                this.render();
            }
        });
    }
);

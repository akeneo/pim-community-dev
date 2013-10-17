/* global define */
define(['underscore'],
function(_) {
    'use strict';

    /**
     * Locale settings
     *
     * @export  oro/locale-settings
     * @class   oro.LocaleSettings
     */
    return {
        settings: {
            addressFormat: {
                en: '%name%\n%organization%\n%street%\n%CITY% %REGION% %COUNTRY% %postal_code%'
            },
            countryToLocale: {
                US: 'en_US'
            },
            nameFormat: {
                en_US: '%prefix% %first_name% %middle_name% %last_name% %suffix%'
            },
            defaultLocale: 'en_US',
            defaultCountry: 'US'
        },

        setSettings: function(settings) {
            this.settings = _.extend(this.settings, settings);
        },

        getAddressFormat: function(country) {
            if (!this.settings.addressFormat.hasOwnProperty(country)) {
                country = this.settings.defaultCountry;
            }
            return this.settings.addressFormat[country];
        },

        getNameFormat: function(locale) {
            if (!this.settings.nameFormat.hasOwnProperty(locale)) {
                locale = this.settings.defaultLocale;
            }
            return this.settings.nameFormat[locale];
        },

        getCountryLocale: function(country) {
            if (this.settings.countryToLocale.hasOwnProperty(country)) {
                return this.settings.countryToLocale[country];
            }
            return this.settings.defaultLocale;
        }
    }
});

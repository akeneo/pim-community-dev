/* global define */
define(['underscore', 'oro/locale-settings/data'],
function(_, settings) {
    'use strict';

    /**
     * Locale settings
     *
     * @export  oro/locale-settings
     * @class   oro.LocaleSettings
     */
    var localeSettings = {
        settings: {
            locale: 'en_US',
            language: 'en',
            country: 'US',
            currency: 'USD',
            timezone: 'UTC',
            timezone_offset: '+00:00',
            format_address_by_address_country: false,
            locale_data: {
                US: {
                    phone_prefix: '1',
                    default_locale: 'en_US',
                    currency_code: 'USD',
                    currency_symbol_prepend: true
                }
            },
            currency_data: {
                USD: {
                    symbol: '$'
                }
            },
            format: {
                datetime: {
                    moment: {
                        'date': 'YYYY-MM-DD',
                        'time': 'HH:mms',
                        'datetime': 'YYYY-MM-DD HH:mm'
                    }
                },
                address: {
                    US: '%name%\n%organization%\n%street%\n%CITY% %REGION% %COUNTRY% %postal_code%'
                },
                name: {
                    en_US: '%prefix% %first_name% %middle_name% %last_name% %suffix%'
                }
            }
        },

        extendSettings: function(settings) {
            var deepExtend = function(target, source) {
                for (var prop in source) if (source.hasOwnProperty(prop)) {
                    if (_.isObject(target[prop])) {
                        target[prop] = deepExtend(target[prop], source[prop]);
                    } else {
                        target[prop] = source[prop];
                    }
                }
                return target;
            };
            this.settings = deepExtend(this.settings, settings);
        },

        getLocale: function() {
            return this.settings.locale;
        },

        getCountry: function() {
            return this.settings.country;
        },

        getTimeZoneOffset: function() {
            return this.settings.timezone_offset;
        },

        getNameFormats: function() {
            return this.settings.format.name;
        },

        getAddressFormats: function() {
            return this.settings.format.address;
        },

        getCountryLocale: function(country) {
            return this.getLocaleData(country, 'default_locale')
                || this.settings.locale;
        },

        getLocaleData: function(country, dataType) {
            if (this.settings.locale_data.hasOwnProperty(country)) {
                return this.settings.locale_data[country][dataType];
            }
            return null;
        },

        isFormatAddressByAddressCountry: function() {
            return this.settings.format_address_by_address_country;
        }
    };

    localeSettings.extendSettings(settings);

    return localeSettings;
});

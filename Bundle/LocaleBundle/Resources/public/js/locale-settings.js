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
        defaults: {
            locale: 'en_US',
            language: 'en',
            country: 'US',
            currency: 'USD',
            timezone: 'UTC',
            timezone_offset: '+00:00'
        },
        settings: {
            locale: 'en_US',
            language: 'en',
            country: 'US',
            currency: 'USD',
            timezone: 'UTC',
            timezone_offset: '+00:00',
            format_address_by_address_country: true,
            locale_data: {
                US: {
                    phone_prefix: '1',
                    default_locale: 'en_US',
                    currency_code: 'USD'
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
                        date: 'YYYY-MM-DD',
                        time: 'HH:mms',
                        datetime: 'YYYY-MM-DD HH:mm'
                    }
                },
                address: {
                    US: '%name%\n%organization%\n%street%\n%CITY% %REGION% %COUNTRY% %postal_code%'
                },
                name: {
                    en_US: '%prefix% %first_name% %middle_name% %last_name% %suffix%'
                }
            },
            calendar: {
                dow: {
                    wide: {
                        1: 'Sunday',
                        2: 'Monday',
                        3: 'Tuesday',
                        4: 'Wednesday',
                        5: 'Thursday',
                        6: 'Friday',
                        7: 'Saturday'
                    },
                    abbreviated: { 1: 'Sun', 2: 'Mon', 3: 'Tue', 4: 'Wed', 5: 'Thu', 6: 'Fri', 7: 'Sat' },
                    short:       { 1: 'Su',  2: 'Mo',  3: 'Tu',  4: 'We',  5: 'Th',  6: 'Fr',  7: 'Sa' },
                    narrow:      { 1: 'S',   2: 'M',   3: 'T',   4: 'W',   5: 'T',   6: 'F',   7: 'S' }
                },
                months: {
                    wide: {
                        1:  'January',
                        2:  'February',
                        3:  'March',
                        4:  'April',
                        5:  'May',
                        6:  'June',
                        7:  'July',
                        8:  'August',
                        9:  'September',
                        10: 'October',
                        11: 'November',
                        12: 'December'
                    },
                    abbreviated: {
                        1:  'Jan',
                        2:  'Feb',
                        3:  'Mar',
                        4:  'Apr',
                        5:  'May',
                        6:  'Jun',
                        7:  'Jul',
                        8:  'Aug',
                        9:  'Sep',
                        10: 'Oct',
                        11: 'Nov',
                        12: 'Dec'
                    },
                    narrow:{
                        1: 'J', 2: 'F', 3: 'M', 4: 'A', 5: 'M', 6: 'J', 7: 'J', 8: 'A', 9: 'S', 10: 'O', 11: 'N', 12: 'D'
                    }
                },
                first_dow: 1
            }
        },

        _deepExtend: function(target, source) {
            for (var prop in source) if (source.hasOwnProperty(prop)) {
                if (_.isObject(target[prop])) {
                    target[prop] = this._deepExtend(target[prop], source[prop]);
                } else {
                    target[prop] = source[prop];
                }
            }
            return target;
        },

        extendSettings: function(settings) {
            this.settings = this._deepExtend(this.settings, settings);
        },

        extendDefaults: function(defaults) {
            this.defaults = this._deepExtend(this.defaults, defaults);
        },

        getLocale: function() {
            return this.settings.locale;
        },

        getCountry: function() {
            return this.settings.country;
        },

        getCurrency: function() {
            return this.settings.currency;
        },

        getCurrencySymbol: function(currencyCode) {
            if (!currencyCode) {
                currencyCode = this.settings.currency;
            }
            if (this.settings.currency_data.hasOwnProperty(currencyCode)) {
                return this.settings.currency_data[currencyCode].symbol
            }
            return currencyCode;
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

        getNumberFormats: function(style) {
            return this.settings.format.number[style];
        },

        getCountryLocale: function(country) {
            return this.getLocaleData(country, 'default_locale') || this.settings.locale;
        },

        /**
         * Gets default vendor specific locale for date time of specific type
         *
         * @param {string} vendor Registered vendor name, for example - "moment" or "jquery_ui"
         * @param {string} type "date"|"datetime"|"time"
         * @param {string} defaultValue
         * @returns {string}
         */
        getVendorDateTimeFormat: function(vendor, type, defaultValue) {
            if (this.settings.format.datetime.hasOwnProperty(vendor)) {
                type = (type && this.settings.format.datetime[vendor].hasOwnProperty(type)) ? type : 'datetime';

                return this.settings.format.datetime[vendor][type];
            }
            return defaultValue;
        },

        getLocaleData: function(country, dataType) {
            if (this.settings.locale_data.hasOwnProperty(country)) {
                return this.settings.locale_data[country][dataType];
            }
            return null;
        },

        isFormatAddressByAddressCountry: function() {
            return this.settings.format_address_by_address_country;
        },

        /**
         * Gets months names array or object.
         *
         * If object then value of key '1' is January, if array first element is January
         *
         * @param {String} [width] "wide" - default |"abbreviated"|"narrow"
         * @param {Boolean} [asArray]
         * @returns {*}
         */
        getCalendarMonthNames: function(width, asArray) {
            width = (width && this.settings.calendar.months.hasOwnProperty(width)) ? width : 'wide';
            var result = this.settings.calendar.months[width];
            if (asArray) {
                result = _.map(result, function(v) { return v });
            }
            return result;
        },

        /**
         * Gets week day names array or object.
         *
         * If object then value of key '1' is Sunday, if array first element is Sunday
         *
         * @param {string} [width] "wide" - default |"abbreviated"|"short"|"narrow"
         * @param {boolean} [asArray] Default false
         * @returns {Object}|{Array}
         */
        getCalendarDayOfWeekNames: function(width, asArray) {
            width = (width && this.settings.calendar.dow.hasOwnProperty(width)) ? width : 'wide';
            var result = this.settings.calendar.dow[width];
            if (asArray) {
                result = _.map(result, function(v) { return v });
            }
            return result;
        },

        /**
         * Gets first day of week starting from 1.
         *
         * @returns {int}
         */
        getCalendarFirstDayOfWeek: function() {
            return this.settings.calendar.first_dow;
        },

        /**
         * Get array of possible locales - first locale is the best, last is the worst
         *
         * @param {string} locale
         * @returns {Array}
         */
        getLocaleFallback: function(locale) {
            var locales = [locale, this.settings.locale, this.defaults.locale];

            var getLocaleLang = function(locale) {
                return locale ? locale.split('_')[0] : locale;
            };

            var possibleLocales = [];
            for (var i = 0; i < locales.length; i++) {
                if (locales[i]) {
                    possibleLocales.push(locales[i]);
                    possibleLocales.push(getLocaleLang(locales[i]));
                }
            }

            return possibleLocales;
        }
    };

    localeSettings.extendSettings(settings);

    return localeSettings;
});

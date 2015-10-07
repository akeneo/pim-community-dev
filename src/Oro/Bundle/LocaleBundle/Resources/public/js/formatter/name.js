/* global define */
define(['oro/locale-settings'],
function(localeSettings) {
    'use strict';

    /**
     * Name formatter
     *
     * @export  oro/formatter/name
     * @name    oro.formatter.name
     */
    return {
        /**
         * @property {Object}
         */
        formats: localeSettings.getNameFormats(),

        /**
         * @property {Object}
         */
        formatCache: {},

        /**
         *
         * @param {Object} person
         * @param {string} locale
         * @returns {string}
         */
        format: function(person, locale) {
            if (!locale) {
                locale = localeSettings.getLocale();
            }

            var format = this.getNameFormat(locale);
            var formatted = format.replace(/%(\w+)%/g, function(pattern, key) {
                var lowerCaseKey = key.toLowerCase();
                var value = '';
                if (person.hasOwnProperty(lowerCaseKey)) {
                    value = person[lowerCaseKey];
                    if (key !== lowerCaseKey) {
                        value = value.toLocaleUpperCase();
                    }
                }
                return value || '';
            });

            return formatted
                .replace(/ +/g, ' ')
                .replace(/^\s+|\s+$/g, '');
        },

        /**
         * @param {string} locale
         * @returns {string}
         */
        getNameFormat: function(locale) {
            if (!this.formatCache.hasOwnProperty(locale)) {
                var localeFallback = localeSettings.getLocaleFallback(locale);

                var format = null;
                for (var i = 0; i < localeFallback.length; i++) {
                    if (this.formats.hasOwnProperty(localeFallback[i])) {
                        format = this.formats[localeFallback[i]];
                        break;
                    }
                }

                if (!format) {
                    throw new Error('Can\'t find name format for locale ' + locale);
                }

                this.formatCache[locale] = format;
            }

            return this.formatCache[locale];
        }
    }
});

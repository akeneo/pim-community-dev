/* global define */
define(['oro/locale-settings'],
function(localeSettings) {
    'use strict';

    /**
     * Name formatter
     *
     * @export  oro/formatter/name
     * @class   oro.NameFormatter
     */
    return {
        format: function(person, locale) {
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
                return value;
            });
            return formatted.replace(/^\s+|\s+$/g, '');
        },

        getNameFormat: function(locale) {
            var nameFormats = localeSettings.getNameFormats();
            if (!nameFormats.hasOwnProperty(locale)) {
                locale = nameFormats.getLocale();
            }
            return nameFormats[locale];
        }
    }
});

/* global define */
define(['oro/locale-settings', 'oro/formatter/name'],
function(localeSettings, nameFormatter) {
    'use strict';

    /**
     * Address formatter
     *
     * @export  oro/formatter/address
     * @name    oro.formatter.address
     */
    return {
        /**
         * @property {Object}
         */
        formats: localeSettings.getAddressFormats(),

        /**
         * @param {Object} address
         * @param {string} country ISO2 code
         * @param {string} newLine
         * @returns {string}
         */
        format: function(address, country, newLine) {
            if (!country) {
                if (localeSettings.isFormatAddressByAddressCountry()) {
                    country = address.country_iso2;
                } else {
                    country = localeSettings.getCountry();
                }
            }
            newLine = newLine || '<br/>';

            var format = this.getAddressFormat(country);
            var formatted = format.replace(/%(\w+)%/g, function (pattern, key) {
                var lowerCaseKey = key.toLowerCase();
                var value = '';
                if ('name' === lowerCaseKey) {
                    value = nameFormatter.format(address, localeSettings.getCountryLocale(country));
                } else if ('street' == lowerCaseKey) {
                    value = address.street + ' ' + (address.street2 || '');
                } else if ('street1' == lowerCaseKey) {
                    value = address.street;
                } else {
                    value = address[lowerCaseKey];
                }
                if (value && key !== lowerCaseKey) {
                    value = value.toLocaleUpperCase();
                }
                return value || '';
            });

            var addressLines = formatted
                .replace(/ *(\\n)+/g, '\\n')
                .split("\\n");
            if (typeof newLine == 'function') {
                for (var i = 0; i < addressLines.length; i++) {
                    addressLines[i] = newLine(addressLines[i]);
                }
                formatted = addressLines.join('');
            } else {
                formatted = addressLines.join(newLine);
            }
            return formatted.replace(/ +/g, ' ');
        },

        /**
         * @param {string} country ISO2 code
         * @returns {*}
         */
        getAddressFormat: function(country) {
            if (!this.formats.hasOwnProperty(country)) {
                country = localeSettings.getCountry();
            }
            return this.formats[country];
        }
    }
});

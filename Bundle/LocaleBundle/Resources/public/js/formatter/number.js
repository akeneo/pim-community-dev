/* global define */
define(['numeral', 'oro/locale-settings'],
function(numeral, localeSettings) {
    'use strict';

    var locale = localeSettings.getLocale();

    /**
     * Number Formatter
     *
     * @export oro/number-formatter
     * @class  oro.NumberFormatter
     */
    var numberFormatter = {
        formatDecimal: function(number) {
            console.log('formatDecimal', number);
            return number;
        },

        formatCurrency: function(number, currency) {
            console.log('formatCurrency', number, currency);
            return number;
        },

        formatInteger: function(number) {
            console.log('formatInteger', number);
            return number;
        },

        formatPercent: function(number) {
            console.log('formatPercent', number);
            return number;
        }
    }

    // load a language
    /*numeral.language('fr', {
        delimiters: {
            thousands: ' ',
            decimal: ','
        },
        abbreviations: {
            thousand: 'k',
            million: 'm',
            billion: 'b',
            trillion: 't'
        },
        ordinal : function (number) {
            return number === 1 ? 'er' : 'ème';
        },
        currency: {
            symbol: '€'
        }
    });
    numeral.language('fr');*/

    // decimal
    console.log('decimal', numeral(123456789.123456).format('0,0.0'));
    console.log('decimal', numeral(-123456789.123456).format('0,0.0'));

    // currency
    console.log('currency', numeral(123456789.123456).format('$0,0.00'));
    console.log('currency', numeral(-123456789.123456).format('$0,0.00'));

    // integer
    console.log('integer', numeral(123456789.123456).format('0,0'));
    console.log('integer', numeral(-123456789.5).format('0,0'));

    // percent
    console.log('percent', numeral(.10).format('0%'));
    console.log('percent', numeral(-.10).format('0%'));
    console.log('percent', numeral(1).format('0,0%'));

    return numberFormatter;
});

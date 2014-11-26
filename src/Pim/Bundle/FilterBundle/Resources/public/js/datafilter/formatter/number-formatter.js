/* global define */
define(['underscore', 'oro/datafilter/abstract-formatter'],
function(_, AbstractFormatter) {
    'use strict';

    /**
     * A floating point number formatter. Doesn't understand notation at the moment.
     *
     * @export  oro/datafilter/number-formatter
     * @class   oro.datafilter.NumberFormatter
     * @extends oro.datafilter.AbstractFormatter
     * @throws {RangeError} If decimals < 0 or > 20.
     */
    var NumberFormatter = function (options) {
        options = options ? _.clone(options) : {};
        _.extend(this, this.defaults, options);

        if (this.decimals < 0 || this.decimals > 20) {
            throw new RangeError("decimals must be between 0 and 20");
        }
    };

    NumberFormatter.prototype = new AbstractFormatter();

    _.extend(NumberFormatter.prototype, {
        /**
         * @memberOf oro.datafilter.NumberFormatter
         * @cfg {Object} options
         *
         * @cfg {number} [options.decimals=2] Number of decimals to display. Must be an integer.
         *
         * @cfg {string} [options.decimalSeparator='.'] The separator to use when
         * displaying decimals.
         *
         * @cfg {string} [options.orderSeparator=','] The separator to use to
         * separator thousands. May be an empty string.
         */
        defaults: {
            decimals: 2,
            decimalSeparator: '.',
            orderSeparator: ','
        },

        HUMANIZED_NUM_RE: /(\d)(?=(?:\d{3})+$)/g,

        /**
         * Takes a floating point number and convert it to a formatted string where
         * every thousand is separated by `orderSeparator`, with a `decimal` number of
         * decimals separated by `decimalSeparator`. The number returned is rounded
         * the usual way.
         *
         * @memberOf oro.datafilter.NumberFormatter
         * @param {number} number
         * @return {string}
         */
        fromRaw: function (number) {
            if (isNaN(number) || number === null) return '';

            number = number.toFixed(~~this.decimals);

            var parts = number.split('.');
            var integerPart = parts[0];
            var decimalPart = parts[1] ? (this.decimalSeparator || '.') + parts[1] : '';

            return integerPart.replace(this.HUMANIZED_NUM_RE, '$1' + this.orderSeparator) + decimalPart;
        },

        /**
         * Takes a string, possibly formatted with `orderSeparator` and/or
         * `decimalSeparator`, and convert it back to a number.
         *
         * @memberOf oro.datafilter.NumberFormatter
         * @param {string} formattedData
         * @return {number|undefined} Undefined if the string cannot be converted to
         * a number.
         */
        toRaw: function (formattedData) {
            var rawData = '';

            var thousands = formattedData.trim().split(this.orderSeparator);
            for (var i = 0; i < thousands.length; i++) {
                rawData += thousands[i];
            }

            var decimalParts = rawData.split(this.decimalSeparator);
            rawData = '';
            for (var i = 0; i < decimalParts.length; i++) {
                rawData = rawData + decimalParts[i] + '.';
            }

            if (rawData[rawData.length - 1] === '.') {
                rawData = rawData.slice(0, rawData.length - 1);
            }

            var result = (rawData * 1).toFixed(~~this.decimals) * 1;
            if (_.isNumber(result) && !_.isNaN(result)) return result;
        }
    });

    return NumberFormatter;
});

/* global define */
define(['backgrid'],
function(Backgrid) {
    'use strict';

    /**
     * Cell formatter that format percent representation
     *
     * @export  oro/datagrid/percent-formatter
     * @class   oro.datagrid.PercentFormatter
     * @extends Backgrid.NumberFormatter
     */
    var PercentFormatter = function () {};

    PercentFormatter.prototype = new Backgrid.NumberFormatter;

    _.extend(PercentFormatter.prototype, {
        /**
         * @inheritDoc
         */
        defaults: {
            decimals: 4,
            decimalSeparator: '.',
            orderSeparator: ','
        },

        /**
         * @inheritDoc
         */
        fromRaw: function (rawData) {
            if (rawData == null || rawData == '') {
                return '';
            }

            var data = rawData * 100;
            var value = Backgrid.NumberFormatter.prototype.fromRaw.call(this, data);

            return value + '%';
        },

        /**
         * @inheritDoc
         */
        toRaw: function (formattedData) {
            var value = formattedData.replace(/%$/, '');
            value = Backgrid.NumberFormatter.prototype.toRaw.call(this, value);

            return value / 100;
        }
    });

    return PercentFormatter;
});

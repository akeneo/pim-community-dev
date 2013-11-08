/* global define */
define(['underscore', 'oro/grid/cell-formatter', 'oro/formatter/number'],
function(_, CellFormatter, formatter) {
    'use strict';

    /**
     * Cell formatter that format percent representation
     *
     * @export oro/grid/number-formatter
     * @class  oro.grid.NumberFormatter
     * @extends oro.grid.CellFormatter
     */
    var NumberFormatter = function (options) {
        options = options ? _.clone(options) : {};
        _.extend(this, options);
        this.formatter = getFormatter(this.style);
    };

    var getFormatter = function(style) {
        var functionName = 'format' + style.charAt(0).toUpperCase() + style.slice(1);
        if (!_.isFunction(formatter[functionName])) {
            throw new Error("Formatter doesn't support '" + style + "' number style");
        }
        return formatter[functionName];
    };

    NumberFormatter.prototype = new CellFormatter();

    _.extend(NumberFormatter.prototype, {
        /** @property {String} */
        style: 'decimal',

        /**
         * @inheritDoc
         */
        fromRaw: function (rawData) {
            if (rawData === null || rawData === '') {
                return '';
            }
            return this.formatter.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        toRaw: function (formattedData) {
            if (formattedData === null || formattedData === '') {
                return null;
            }
            return formatter.unformat(formattedData);
        }
    });

    return NumberFormatter;
});

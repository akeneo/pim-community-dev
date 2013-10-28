/* global define */
define(['underscore', 'oro/datagrid/cell-formatter', 'oro/formatter/number'],
function(_, CellFormatter, formatter) {
    'use strict';

    /**
     * Cell formatter that format percent representation
     *
     * @export oro/datagrid/number-formatter
     * @class  oro.datagrid.NumberFormatter
     * @extends oro.datagrid.CellFormatter
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
            return this.formatter.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        toRaw: function (formattedData) {
            return formatter.unformat(formattedData);
        }
    });

    return NumberFormatter;
});

/* global define */
define(['underscore', 'backgrid', 'oro/formatter/datetime'],
function(_, Backgrid, DateTimeFormatter) {
    'use strict';

    /**
     * Date formatter for date cell
     *
     * @export  oro/datagrid/date-formatter
     * @class   oro.datagrid.DateTimeFormatter
     * @extends Backgrid.CellFormatter
     */
    var DatagridDateTimeFormatter = function (options) {
        _.extend(this, options);
    };

    DatagridDateTimeFormatter.prototype = new Backgrid.CellFormatter();
    _.extend(DatagridDateTimeFormatter.prototype, {
        /**
         * Allowed types are "date", "time" and "dateTime"
         *
         * @property {string}
         */
        type: 'dateTime',

        /**
         * @inheritDoc
         */
        fromRaw: function (rawData) {
            if (rawData == null || rawData == '') {
                return '';
            }
            return this._getFormatterFunction('format').call(DateTimeFormatter, rawData);
        },

        /**
         * @inheritDoc
         */
        toRaw: function (formattedData) {
            if (formattedData == null || formattedData == '') {
                return null;
            }

            return this._getFormatterFunction('unformat').call(DateTimeFormatter, formattedData);
        },

        /**
         * @param {string} prefix
         * @returns {Function}
         * @private
         */
        _getFormatterFunction: function(prefix) {
            function capitaliseFirstLetter(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }

            var functionName = prefix + capitaliseFirstLetter(this.type);
            if (!DateTimeFormatter.hasOwnProperty(functionName)
                || typeof DateTimeFormatter[functionName] != 'function'
                ) {
                throw new Error('Can\'t use formatter function with name ' + functionName);
            }

            return DateTimeFormatter[functionName];
        }
    });

    return DatagridDateTimeFormatter;
});

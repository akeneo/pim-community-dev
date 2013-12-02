/* global define */
define(['underscore', 'backgrid', 'oro/formatter/datetime'],
function(_, Backgrid, DateTimeFormatter) {
    'use strict';

    /**
     * Date formatter for date cell
     *
     * @export  oro/grid/date-formatter
     * @class   oro.grid.DateTimeFormatter
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
            // Call one of formatDate formatTime formatDateTime
            return this._getFormatterFunction('format').call(DateTimeFormatter, rawData);
        },

        /**
         * @inheritDoc
         */
        toRaw: function (formattedData) {
            if (formattedData == null || formattedData == '') {
                return null;
            }

            // Call one of  convertDateToBackendFormat, convertTimeToBackendFormat, convertDateTimeToBackendFormat
            return this._getFormatterFunction('convert', 'ToBackendFormat').call(DateTimeFormatter, formattedData);
        },

        /**
         * @param {string} prefix
         * @param {string} [suffix]
         * @returns {Function}
         * @private
         */
        _getFormatterFunction: function(prefix, suffix) {
            suffix = suffix || '';

            function capitaliseFirstLetter(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }

            var functionName = prefix + capitaliseFirstLetter(this.type) + suffix;
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

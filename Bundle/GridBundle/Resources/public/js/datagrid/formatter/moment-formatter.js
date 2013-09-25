/* global define */
define(['underscore', 'backgrid', 'backgrid/moment'],
function(_, Backgrid) {
    'use strict';

    /**
     * Formatter for date and time. Fixed formatting method.
     *
     * @export  oro/datagrid/moment-formatter
     * @class   oro.datagrid.MomentFormatter
     * @extends Backgrid.Extension.MomentFormatter
     */
    var MomentFormatter = function (options) {
        _.extend(this, this.defaults, options);
    };

    MomentFormatter.prototype = new Backgrid.Extension.MomentFormatter();

    _.extend(MomentFormatter.prototype, {
        defaults: {
            modelInUTC: true,
            modelLang: moment.lang(),
            modelFormat: moment.defaultFormat,
            displayInUTC: false,
            displayLang: moment.lang(),
            displayFormat: moment.defaultFormat,
            displayTimeZone: null
        },

        /**
         * Converts datetime values from the model for display.
         *
         * @member Backgrid.Extension.MomentFormatter
         * @param {*} rawData
         * @return {string}
         */
        fromRaw: function (rawData) {
            if (!rawData) {
                return '';
            }

            var m;

            // moment.js doesn't support switch lang locally yet as of 1.7.2
            // I don't know what kind of subtle nasty bugs this will bring.
            // See [here](https://github.com/timrwood/moment/issues/508#issuecomment-10768334).
            if (this.modelLang) {
                var oldLang = moment.lang();
                moment.lang(this.modelLang);
                m = this.modelInUTC ? moment.utc(rawData, this.modelFormat) : moment(rawData, this.modelFormat);
                moment.lang(oldLang);
            }
            else {
                m = this.modelInUTC ? moment.utc(rawData, this.modelFormat) : moment(rawData, this.modelFormat);
            }

            if (this.displayLang) {
                m.lang(this.displayLang);
            }

            if (this.displayInUTC) {
                m.utc();
            } else if (!_.isNull(this.displayTimeZone)) {
                m.zone(this.displayTimeZone)
            } else {
                m.local();
            }

            return m.format(this.displayFormat);
        },

        /**
         * Converts datetime values from user input to model values.
         *
         * @member Backgrid.Extension.MomentFormatter
         * @param {string} formattedData
         * @return {string}
         */
        toRaw: function (formattedData) {
            var m;

            if (this.displayLang) {
                var oldLang = moment.lang();
                moment.lang(this.displayLang);
                m = this.displayInUTC ? moment.utc(formattedData, this.displayFormat) : moment(formattedData, this.displayFormat);
                moment.lang(oldLang);
            }
            else {
                m = this.displayInUTC ? moment.utc(formattedData, this.displayFormat) : moment(formattedData, this.displayFormat);
            }

            if (!m || !m.isValid()) return;

            if (this.modelLang) {
                m.lang(this.modelLang);
            }

            if (this.displayInUTC) {
                m.utc();
            } else if (!_.isNull(this.displayTimeZone)) {
                m.zone(this.displayTimeZone)
            } else {
                m.local();
            }

            return m.format(this.modelFormat);
        }
    });

    return MomentFormatter;
});

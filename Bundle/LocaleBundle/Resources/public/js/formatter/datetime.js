/* global define */
define(['oro/locale-settings', 'moment'],
function(localeSettings, moment) {
    'use strict';

    /**
     * Datetime formatter
     *
     * @export  oro/formatter/datetime
     * @class   oro.DatetimeFormatter
     */
    return {
        /**
         * @property {Object}
         */
        momentFormats: localeSettings.getDateTimeFormats('moment'),

        /**
         * @returns {String}
         */
        getDateFormat: function() {
            return this.momentFormats.date;
        },

        /**
         * @returns {String}
         */
        getTimeFormat: function() {
            return this.momentFormats.time;
        },

        /**
         * @returns {String}
         */
        getDateTimeFormat: function() {
            return this.momentFormats.datetime;
        },

        /**
         * @param {String} value
         * @returns {*}
         */
        isDateValid: function(value) {
            return moment(value, this.getDateFormat(), true).isValid();
        },

        /**
         * @param {String} value
         * @returns {Boolean}
         */
        isTimeValid: function(value) {
            return moment(value, this.getTimeFormat(), true).isValid();
        },

        /**
         * @param {String} value
         * @returns {Boolean}
         */
        isDateTimeValid: function(value) {
            return moment(value, this.getDateTimeFormat(), true).isValid();
        },

        /**
         * @param {String} value
         * @returns {String}
         */
        formatDate: function(value) {
            var momentDate = moment(value);
            if (!momentDate.isValid()) {
                throw new Error('Invalid date ' + value);
            }

            return momentDate.format(this.getDateFormat());
        },

        /**
         * @param {String} value
         * @returns {String}
         */
        formatTime: function(value) {
            var momentTime = moment(value, ['HH:mm:ss', 'HH:mm']);
            if (!momentTime.isValid()) {
                throw new Error('Invalid time ' + value);
            }

            return momentTime.format(this.getTimeFormat());
        },

        /**
         * @param {String} value
         * @returns {String}
         */
        formatDateTime: function(value) {
            var momentDateTime = moment(value);
            if (!momentDateTime.isValid()) {
                throw new Error('Invalid datetime ' + value);
            }

            return momentDateTime.format(this.getDateTimeFormat());
        }
    }
});

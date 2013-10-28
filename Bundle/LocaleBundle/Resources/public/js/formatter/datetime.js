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
         * @property {string}
         */
        timezoneOffset: localeSettings.getTimeZoneOffset(),

        /**
         * @returns {string}
         */
        getDateFormat: function() {
            return this.momentFormats.date;
        },

        /**
         * @returns {string}
         */
        getTimeFormat: function() {
            return this.momentFormats.time;
        },

        /**
         * @returns {string}
         */
        getDateTimeFormat: function() {
            return this.momentFormats.datetime;
        },

        /**
         * @param {string} value
         * @returns {*}
         */
        isDateValid: function(value) {
            return moment(value, this.getDateFormat(), true).isValid();
        },

        /**
         * @param {string} value
         * @returns {Boolean}
         */
        isTimeValid: function(value) {
            return moment(value, this.getTimeFormat(), true).isValid();
        },

        /**
         * @param {string} value
         * @returns {Boolean}
         */
        isDateTimeValid: function(value) {
            return moment(value, this.getDateTimeFormat(), true).isValid();
        },

        /**
         * @param {string} value
         * @returns {string}
         */
        formatDate: function(value) {
            var momentDate = moment(value);
            if (!momentDate.isValid()) {
                throw new Error('Invalid date ' + value);
            }

            return momentDate.format(this.getDateFormat());
        },

        /**
         * @param {string} value
         * @returns {string}
         */
        formatTime: function(value) {
            var momentTime = moment(value, ['HH:mm:ss', 'HH:mm']);
            if (!momentTime.isValid()) {
                throw new Error('Invalid time ' + value);
            }

            return momentTime.format(this.getTimeFormat());
        },

        /**
         * @param {string} value
         * @returns {string}
         */
        formatDateTime: function(value) {
            var momentDateTime = moment(value);
            if (!momentDateTime.isValid()) {
                throw new Error('Invalid datetime ' + value);
            }

            return momentDateTime.zone(this.timezoneOffset).format(this.getDateTimeFormat());
        }
    }
});

/* global define */
define(['oro/locale-settings', 'moment'],
function(localeSettings, moment) {
    'use strict';

    var datetimeVendor = 'moment';

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
        frontendFormats: {
            'date':     localeSettings.getVendorDateTimeFormat(datetimeVendor, 'date'),
            'time':     localeSettings.getVendorDateTimeFormat(datetimeVendor, 'time'),
            'datetime': localeSettings.getVendorDateTimeFormat(datetimeVendor, 'datetime')
        },

        /**
         * @property {Object}
         */
        backendFormats: {
            'date':     'YYYY-MM-DD',
            'time':     'HH:mm:ss',
            'datetime': 'YYYY-MM-DD[T]HH:mm:ssZZ'
        },

        /**
         * @property {string}
         */
        timezoneOffset: localeSettings.getTimeZoneOffset(),

        /**
         * @returns {string}
         */
        getDateFormat: function() {
            return this.frontendFormats.date;
        },

        /**
         * @returns {string}
         */
        getTimeFormat: function() {
            return this.frontendFormats.time;
        },

        /**
         * @returns {string}
         */
        getDateTimeFormat: function() {
            return this.frontendFormats.datetime;
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
                throw new Error('Invalid backend date ' + value);
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
                throw new Error('Invalid backend time ' + value);
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
                throw new Error('Invalid backend datetime ' + value);
            }

            return momentDateTime.zone(this.timezoneOffset).format(this.getDateTimeFormat());
        },

        /**
         * @param {string} value
         * @returns {string}
         */
        unformatDate: function(value) {
            if (!this.isDateValid(value)) {
                throw new Error('Invalid frontend date ' + value);
            }

            return moment(value, this.getDateFormat()).format(this.backendFormats.date);
        },

        /**
         * @param {string} value
         * @returns {string}
         */
        unformatTime: function(value) {
            if (!this.isTimeValid(value)) {
                throw new Error('Invalid frontend time ' + value);
            }

            return moment(value, this.getTimeFormat()).format(this.backendFormats.time);
        },

        /**
         * @param {string} value
         * @returns {string}
         */
        unformatDateTime: function(value) {
            if (!this.isDateTimeValid(value)) {
                throw new Error('Invalid frontend datetime ' + value);
            }

            return moment(value, this.getDateTimeFormat()).zone('+00:00').format(this.backendFormats.datetime);
        }
    }
});

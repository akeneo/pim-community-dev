/* global define */
define(['oro/locale-settings', 'moment'],
function(localeSettings, moment) {
    'use strict';

    var datetimeVendor = 'moment';

    /**
     * Datetime formatter
     *
     * @export  oro/formatter/datetime
     * @name    oro.formatter.datetime
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
            return moment(value, this.getDateFormat()).isValid();
        },

        /**
         * @param {string} value
         * @returns {Boolean}
         */
        isTimeValid: function(value) {
            return moment(value, this.getTimeFormat()).isValid();
        },

        /**
         * @param {string} value
         * @returns {Boolean}
         */
        isDateTimeValid: function(value) {
            return moment(value, this.getDateTimeFormat()).isValid();
        },

        /**
         * @param {string} value
         * @returns {string}
         */
        formatDate: function(value) {
            return this.getMomentForBackendDate(value).format(this.getDateFormat());
        },

        /**
         * Get Date object based on formatted backend date string
         *
         * @param {string} value
         * @returns {Date}
         */
        unformatBackendDate: function(value) {
            return this.getMomentForBackendDate(value).toDate();
        },

        /**
         * Get moment object based on formatted backend date string
         *
         * @param {string} value
         * @returns {moment}
         */
        getMomentForBackendDate: function(value) {
            var momentDate = moment.utc(value);
            if (!momentDate.isValid()) {
                throw new Error('Invalid backend date ' + value);
            }
            return momentDate;
        },

        /**
         * @param {string} value
         * @returns {string}
         */
        formatTime: function(value) {
            return this.getMomentForBackendTime(value).format(this.getTimeFormat());
        },

        /**
         * Get Date object based on formatted backend time string
         *
         * @param {string} value
         * @returns {Date}
         */
        unformatBackendTime: function(value) {
            return this.getMomentForBackendTime(value).toDate();
        },

        /**
         * Get moment object based on formatted backend date string
         *
         * @param {string} value
         * @returns {moment}
         */
        getMomentForBackendTime: function(value) {
            var momentTime = moment.utc(value, ['HH:mm:ss', 'HH:mm']);
            if (!momentTime.isValid()) {
                throw new Error('Invalid backend time ' + value);
            }
            return momentTime;
        },

        /**
         * @param {string} value
         * @returns {string}
         */
        formatDateTime: function(value) {
            return this.getMomentForBackendDateTime(value).format(this.getDateTimeFormat());
        },

        /**
         * Get Date object based on formatted backend date time string
         *
         * @param {string} value
         * @returns {Date}
         */
        unformatBackendDateTime: function(value) {
            return this.getMomentForBackendDateTime(value).toDate();
        },

        /**
         * Get moment object based on formatted backend date time string
         *
         * @param {string} value
         * @returns {moment}
         */
        getMomentForBackendDateTime: function(value) {
            var momentDateTime = moment.utc(value).zone(this.timezoneOffset);
            if (!momentDateTime.isValid()) {
                throw new Error('Invalid backend datetime ' + value);
            }
            return momentDateTime;
        },

        /**
         * @param {string} value
         * @returns {string}
         */
        convertDateToBackendFormat: function(value) {
            return this.getMomentForFrontendDate(value).format(this.backendFormats.date);
        },

        /**
         * @param {string} value
         * @returns {string}
         */
        convertTimeToBackendFormat: function(value) {
            return this.getMomentForFrontendTime(value).format(this.backendFormats.time);
        },

        /**
         * @param {string} value
         * @param {string} [timezoneOffset]
         * @returns {string}
         */
        convertDateTimeToBackendFormat: function(value, timezoneOffset) {
            return this.getMomentForFrontendDateTime(value, timezoneOffset).format(this.backendFormats.datetime);
        },

        /**
         * Get moment object based on formatted frontend date string
         *
         * @param {string} value
         * @returns {moment}
         */
        getMomentForFrontendDate: function(value) {
            if (this.isDateObject(value)) {
                return this.formatDate(value);
            } else if (!this.isDateValid(value)) {
                throw new Error('Invalid frontend date ' + value);
            }

            return moment.utc(value, this.getDateFormat());
        },

        /**
         * Get Date object based on formatted frontend date string
         *
         * @param {string} value
         * @returns {Date}
         */
        unformatDate: function(value) {
            return this.getMomentForFrontendDate(value).toDate();
        },

        /**
         * Get moment object based on formatted frontend time string
         *
         * @param {string} value
         * @returns {moment}
         */
        getMomentForFrontendTime: function(value) {
            if (this.isDateObject(value)) {
                value = this.formatTime(value);
            } else if (!this.isTimeValid(value)) {
                throw new Error('Invalid frontend time ' + value);
            }

            return moment.utc(value, this.getTimeFormat());
        },

        /**
         * Get Date object based on formatted frontend time string
         *
         * @param {string} value
         * @returns {Date}
         */
        unformatTime: function(value) {
            return this.getMomentForFrontendTime(value).toDate();
        },

        /**
         * Get moment object based on formatted frontend date time string
         *
         * @param {string} value
         * @param {string} [timezoneOffset]
         * @returns {moment}
         */
        getMomentForFrontendDateTime: function(value, timezoneOffset) {
            if (this.isDateObject(value)) {
                value = this.formatDateTime(value);
            } else if (!this.isDateTimeValid(value)) {
                throw new Error('Invalid frontend datetime ' + value);
            }

            timezoneOffset = timezoneOffset || this.timezoneOffset;

            var datetimeFormat = this.getDateTimeFormat();
            // tell which timezone must be used
            if (datetimeFormat.indexOf('Z') === -1) {
                datetimeFormat += ' Z';
                value += ' ' + timezoneOffset;
            }

            return moment.utc(value, datetimeFormat).zone(timezoneOffset);
        },

        /**
         * Get Date object based on formatted frontend date time string
         *
         * @param {string} value
         * @param {string} [timezoneOffset]
         * @returns {Date}
         */
        unformatDateTime: function(value, timezoneOffset) {
            return this.getMomentForFrontendDateTime(value, timezoneOffset).toDate();
        },

        /**
         * Check that obj is Date object
         *
         * @private
         * @param {string|Date} obj
         * @returns {boolean}
         */
        isDateObject: function(obj) {
            return Object.prototype.toString.call(obj) == '[object Date]'
        }
    }
});

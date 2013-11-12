/* global define */
define(['jquery', 'underscore', 'oro/datafilter/date-filter', 'oro/locale-settings'],
    function($, _, DateFilter, localeSettings) {
    'use strict';
    /**
     * Datetime filter: filter type as option + interval begin and end dates
     *
     * @export  oro/datafilter/datetime-filter
     * @class   oro.datafilter.DatetimeFilter
     * @extends oro.datafilter.DateFilter
     */
    return DateFilter.extend({
        /**
         * CSS class for visual datetime input elements
         *
         * @property
         */
        inputClass: 'datetime-visual-element',

        /**
         * Datetime widget options
         *
         * @property
         */
        dateWidgetOptions: _.extend({
            timeFormat: localeSettings.getVendorDateTimeFormat('jquery_ui', 'time', 'HH:mm'),
            altFieldTimeOnly: false,
            altSeparator: ' ',
            altTimeFormat: 'HH:mm'
        }, DateFilter.prototype.dateWidgetOptions),

        /**
         * @inheritDoc
         */
        _initializeDateWidget: function(widgetSelector) {
            this.$(widgetSelector).datetimepicker(this.dateWidgetOptions);
            var widget = this.$(widgetSelector).datetimepicker('widget');
            widget.addClass(this.dateWidgetOptions.className);
            $(this.dateWidgetSelector).on('click', function(e) {
                e.stopImmediatePropagation();
            });
            return widget;
        },

        /**
         * @inheritDoc
         */
        _formatDisplayValue: function(value) {
            var dateFromFormat = this.dateWidgetOptions.altFormat;
            var dateToFormat = this.dateWidgetOptions.dateFormat;
            var timeFromFormat = this.dateWidgetOptions.altTimeFormat;
            var timeToFormat = this.dateWidgetOptions.timeFormat;
            return this._formatValueDatetimes(value, dateFromFormat, dateToFormat, timeFromFormat, timeToFormat);
        },

        /**
         * @inheritDoc
         */
        _formatRawValue: function(value) {
            var dateFromFormat = this.dateWidgetOptions.dateFormat;
            var dateToFormat = this.dateWidgetOptions.altFormat;
            var timeFromFormat = this.dateWidgetOptions.timeFormat;
            var timeToFormat = this.dateWidgetOptions.altTimeFormat;
            return this._formatValueDatetimes(value, dateFromFormat, dateToFormat, timeFromFormat, timeToFormat);
        },

        /**
         * Format datetimes in a valut to another format
         *
         * @param {Object} value
         * @param {String} dateFromFormat
         * @param {String} dateToFormat
         * @param {String} timeFromFormat
         * @param {String} timeToToFormat
         * @return {Object}
         * @protected
         */
        _formatValueDatetimes: function(value, dateFromFormat, dateToFormat, timeFromFormat, timeToToFormat) {
            if (value.value && value.value.start) {
                value.value.start = this._formatDatetime(
                    value.value.start, dateFromFormat, dateToFormat, timeFromFormat, timeToToFormat
                );
            }
            if (value.value && value.value.end) {
                value.value.end = this._formatDatetime(
                    value.value.end, dateFromFormat, dateToFormat, timeFromFormat, timeToToFormat
                );
            }
            return value;
        },

        /**
         * Formats datetime string to another format
         *
         * @param {String} value
         * @param {String} dateFromFormat
         * @param {String} dateToFormat
         * @param {String} timeFromFormat
         * @param {String} timeToToFormat
         * @return {String}
         * @protected
         */
        _formatDatetime: function(value, dateFromFormat, dateToFormat, timeFromFormat, timeToToFormat) {
            var datePart = this._formatDate(value, dateFromFormat, dateToFormat);
            var dateBefore = this._formatDate(datePart, dateToFormat, dateFromFormat);
            var timePart = value.substr(dateBefore.length + this.dateWidgetOptions.altSeparator.length);
            timePart = this._formatTime(timePart, timeFromFormat, timeToToFormat);
            return datePart + this.dateWidgetOptions.altSeparator + timePart;
        },

        /**
         * Formats time string to another format
         *
         * @param {String} value
         * @param {String} fromFormat
         * @param {String} toFormat
         * @return {String}
         * @protected
         */
        _formatTime: function(value, fromFormat, toFormat) {
            var fromValue = $.datepicker.parseTime(fromFormat, value);
            if (!fromValue) {
                fromValue = $.datepicker.parseTime(toFormat, value);
                if (!fromValue) {
                    return value;
                }
            }
            return $.datepicker.formatTime(toFormat, fromValue);
        }
    });
});

'use strict';

/**
 * Format a date according to specified format.
 * It instantiates a datepicker on-the-fly to perform the conversion.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'jquery',
        'underscore',
        'datepicker',
        'pim/date-context'
    ], function (
        $,
        _,
        Datepicker,
        DateContext
    ) {
        return {
            /**
             * Date widget options
             */
            datetimepickerOptions: {
                format: DateContext.get('date').format,
                defaultFormat: DateContext.get('date').defaultFormat,
                language: DateContext.get('language')
            },

            /**
             * Format a date according to specified format.
             * It instantiates a datepicker on-the-fly to perform the conversion. Not possible to use the "real"
             * ones since we need to format a date even when the UI is not initialized yet.
             *
             * @param {String} date
             * @param {String} fromFormat
             * @param {String} toFormat
             *
             * @return {String}
             */
            format: function (date, fromFormat, toFormat) {
                if (_.isEmpty(date) || _.isUndefined(date) || _.isArray(date)) {
                    return null;
                }

                var options = $.extend({}, this.datetimepickerOptions, {format: fromFormat});
                var fakeDatepicker = Datepicker.init($('<input>'), options).data('datetimepicker');

                if (null !== fakeDatepicker.parseDate(date)) {
                    fakeDatepicker.setValue(date);
                    fakeDatepicker.format = toFormat;
                    fakeDatepicker._compileFormat();
                }

                return fakeDatepicker.formatDate(fakeDatepicker.getDate());
            }
        };
    }
);

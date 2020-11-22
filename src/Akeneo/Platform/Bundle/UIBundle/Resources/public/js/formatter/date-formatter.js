'use strict';
import {dateContext} from 'pimui/js/date-context';

/**
 * Format a date according to specified format.
 * It instantiates a datepicker on-the-fly to perform the conversion.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['jquery', 'underscore', 'datepicker'], function ($, _, Datepicker) {
  return {
    /**
     * Date widget options
     */
    datetimepickerOptions: {
      format: dateContext.get('date').format,
      defaultFormat: dateContext.get('date').defaultFormat,
      language: dateContext.get('language'),
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
    },
  };
});

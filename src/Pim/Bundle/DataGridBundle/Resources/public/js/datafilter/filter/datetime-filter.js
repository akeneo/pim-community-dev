/* global define */
import $ from 'jquery';
import _ from 'underscore';
import DateFilter from 'oro/datafilter/date-filter';
import DateContext from 'pim/date-context';

/**
 * Datetime filter: filter type as option + interval begin and end dates
 *
 * @export  oro/datafilter/datetime-filter
 * @class   oro.datafilter.DatetimeFilter
 * @extends oro.datafilter.DateFilter
 */
export default DateFilter.extend({
  /**
   * CSS class for visual datetime input elements
   *
   * @property
   */
  inputClass: 'AknTextField',

  /**
   * Date widget options
   *
   * @property
   */
  datetimepickerOptions: {
    format: DateContext.get('time').format,
    defaultFormat: DateContext.get('time').defaultFormat,
    language: DateContext.get('language'),
    pickTime: true,
    pickSeconds: false,
    pick12HourFormat: DateContext.get('12_hour_format'),
  }
});


/* global define */
define(['jquery', 'underscore', 'oro/datafilter/date-filter'],
    function($, _, DateFilter) {
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
         * Date widget options
         *
         * @property
         */
        datetimepickerOptions: {
            format: 'yyyy-MM-dd hh:mm',
            defaultFormat: 'yyyy-MM-dd hh:mm',
            locale: 'en',
            pickSeconds: false
        }
    });
});

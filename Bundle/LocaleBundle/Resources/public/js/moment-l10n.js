/* global define */
define(['moment', 'oro/locale-settings'],
function(moment, localeSettings) {
    'use strict';

    var locale = localeSettings.getLocale();

    moment.lang(locale, {
        months : localeSettings.getCalendarMonthNames('wide', true),
        monthsShort : localeSettings.getCalendarMonthNames('abbreviated', true),
        weekdays : localeSettings.getCalendarDayOfWeekNames('wide', true),
        weekdaysShort : localeSettings.getCalendarDayOfWeekNames('abbreviated', true),
        weekdaysMin : localeSettings.getCalendarDayOfWeekNames('short', true),
        week : {
            dow : localeSettings.getCalendarFirstDayOfWeek() - 1
        }
    });

    return moment;

});

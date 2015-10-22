/* global define */
define(['moment', 'oro/translator', 'oro/locale-settings'],
function(moment, __, localeSettings) {
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
        },
        meridiem : function (hours, minutes, isLower) {
            if (hours > 11) {
                return isLower ? __('pm') : __('PM');
            } else {
                return isLower ? __('am') : __('AM');
            }
        }
    });

    return moment;
});

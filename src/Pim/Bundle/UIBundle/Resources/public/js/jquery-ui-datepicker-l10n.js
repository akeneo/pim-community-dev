/* global define */
define(['jquery', 'oro/translator', 'oro/locale-settings', 'jquery-ui'],
function($, __, localeSettings) {
    'use strict';

    var locale = localeSettings.getLocale();

    $.datepicker.regional[locale] = {
        closeText: __("Done"), // Display text for close link
        prevText: __("Prev"), // Display text for previous month link
        nextText: __("Next"), // Display text for next month link
        currentText: __("Today"), // Display text for current month link
        // ["January","February","March","April","May","June", "July",
        // "August","September","October","November","December"]
        // Names of months for drop-down and formatting
        monthNames: localeSettings.getCalendarMonthNames('wide', true),
        // ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"] For formatting
        monthNamesShort: localeSettings.getCalendarMonthNames('abbreviated', true),
        // ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"] For formatting
        dayNames: localeSettings.getCalendarDayOfWeekNames('wide', true),
        // ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"] For formatting
        dayNamesShort: localeSettings.getCalendarDayOfWeekNames('abbreviated', true),
        // ["Su","Mo","Tu","We","Th","Fr","Sa"] Column headings for days starting at Sunday
        dayNamesMin: localeSettings.getCalendarDayOfWeekNames('short', true),
        weekHeader: __("Wk"), // Column header for week of the year
        dateFormat: localeSettings.getVendorDateTimeFormat('jquery_ui', 'date', 'mm/dd/yy'), // See format options on parseDate
        firstDay: localeSettings.getCalendarFirstDayOfWeek() - 1 // The first day of the week, Sun = 0, Mon = 1, ...
        //isRTL: false, // True if right-to-left language, false if left-to-right
        //showMonthAfterYear: false, // True if the year select precedes month, false for month then year
        //yearSuffix: "" // Additional text to append to the year in the month headers
    };
    $.datepicker.setDefaults($.datepicker.regional[locale]);
});

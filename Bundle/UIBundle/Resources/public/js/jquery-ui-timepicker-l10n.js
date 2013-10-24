/* global define */
define(['jquery', 'oro/translator', 'oro/locale-settings', 'jquery-ui-timepicker'],
function($, __, localeSettings) {
    'use strict';

    var locale = localeSettings.locale;

    $.timepicker.regional[locale] = { // Default regional settings
        currentText: __('Now'),
        closeText: __('Done'),
        amNames: [__('AM'), __('A')],
        pmNames: [__('PM'), __('P')],
        timeFormat: localeSettings.getVendorDateTimeFormat('jquery_ui', 'time', 'HH:mm'),
        timeSuffix: '',
        timeOnlyTitle: __('Choose Time'),
        timeText: __('Time'),
        hourText: __('Hour'),
        minuteText: __('Minute'),
        secondText: __('Second'),
        millisecText: __('Millisecond'),
        microsecText: __('Microsecond'),
        timezoneText: __('Time Zone')
    };
    $.timepicker.setDefaults($.timepicker.regional[locale]);
});

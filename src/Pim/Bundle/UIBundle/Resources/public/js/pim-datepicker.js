define(
    ['jquery', 'pim/date-context', 'bootstrap.datetimepicker'],
    function ($, DateContext) {
        'use strict';

        var datetimepickerOptions = {
            format: DateContext.get('date').format,
            language: DateContext.get('language'),
            pickTime: false
        };

        var init = function (id) {
            var $field = $('#' + id).closest('div');

            $field.datetimepicker(datetimepickerOptions);
        };

        return {
            init: init
        };
    }
);

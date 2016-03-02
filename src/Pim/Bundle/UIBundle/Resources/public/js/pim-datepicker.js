define(
    ['jquery', 'bootstrap.datetimepicker'],
    function ($) {
        'use strict';

        var datetimepickerOptions = {
            format: 'yyyy-MM-dd',
            language: 'en',
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

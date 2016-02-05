define(
    ['jquery', 'pim/date-context', 'bootstrap.datetimepicker'],
    function ($, DateContext) {
        'use strict';

        return {
            options: {
                format: DateContext.get('date').format,
                defaultFormat: DateContext.get('date').defaultFormat,
                locale: DateContext.get('language'),
                pickTime: false
            },
            init: function ($target, options) {
                options = $.extend(true, this.options, options);

                $target.datetimepicker(options);

                return $target;
            }
        };
    }
);

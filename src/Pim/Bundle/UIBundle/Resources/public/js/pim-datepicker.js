define(
    ['jquery', 'underscore', 'pim/date-context', 'bootstrap.datetimepicker'],
    function ($, _, DateContext) {
        'use strict';

        return {
            options: {
                format: DateContext.get('date').format,
                defaultFormat: DateContext.get('date').defaultFormat,
                language: DateContext.get('language'),
                pickTime: false
            },
            init: function ($target, options) {
                options = $.extend(true, this.options, options);

                if (('en' !== options.language) && (undefined === $.fn.datetimepicker.dates[options.language])) {
                    var languageOptions = {};
                    var defaultOptions = $.fn.datetimepicker.dates.en;

                    _.each(_.keys(defaultOptions), function (key) {
                        languageOptions[key] = [];
                        _.each(defaultOptions[key], function (value) {
                            languageOptions[key].push(_.__('datetimepicker.' + key + '.' + value));
                        });
                    });

                    $.fn.datetimepicker.dates[options.language] = languageOptions;
                }

                $target.datetimepicker(options);

                return $target;
            }
        };
    }
);

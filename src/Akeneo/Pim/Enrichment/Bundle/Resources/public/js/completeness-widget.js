define(
    [
        'jquery',
        'underscore',
        'pim/i18n',
        'pim/user-context',
        'pim/dashboard/abstract-widget',
        'pim/dashboard/template/completeness-widget'
    ],
    function ($, _, i18n, UserContext, AbstractWidget, template) {
        'use strict';

        return AbstractWidget.extend({
            id: 'completeness-widget',
            template: _.template(template),

            _processResponse: function (data) {
                var channelArray = [];
                _.each(data, function (channel, channelCode) {
                    channel.name = i18n.getLabel(
                        channel.labels,
                        UserContext.get('catalogLocale'),
                        channelCode
                    );
                    channel.locales = channel.locales || {};
                    var divider = channel.total * _.keys(channel.locales).length;

                    channel.percentage = divider === 0 ?
                        0 :
                        Math.round(channel.complete / divider * 100);

                    _.each(channel.locales, function (localeCompleteCount, localeLabel) {
                        var divider = channel.total;
                        var ratio = divider === 0 ?
                            0 :
                            Math.round(localeCompleteCount / divider * 100);

                        channel.locales[localeLabel] = { ratio: ratio };
                    });

                    channelArray.push(channel);
                });

                return channelArray;
            }
        });
    }
);

define(
    ['jquery', 'underscore', 'pim/dashboard/abstract-widget', 'text!pimdashboard/templates/completeness-widget'],
    function ($, _, AbstractWidget, template) {
        'use strict';

        return AbstractWidget.extend({
            tagName: 'table',

            id: 'completeness-widget',

            options: {
                completeBar: 'bar-success',
                inCompleteBar: 'bar-warning'
            },

            template: _.template(template),

            events: {
                'click a[data-toggle-channel]': 'toggleChannel'
            },

            toggleChannel: function (e) {
                e.preventDefault();

                var channel = $(e.currentTarget).data('toggle-channel');
                this.$('tr[data-channel="' + channel + '"]').toggle();
                this.$('a[data-toggle-channel="' + channel + '"] i')
                    .toggleClass('icon-caret-right icon-caret-down');
            },

            _processResponse: function (data) {
                _.each(data, function (channelResult) {
                    channelResult.locales = channelResult.locales || {};
                    var divider = channelResult.total * _.keys(channelResult.locales).length;

                    channelResult.percentage = divider === 0 ?
                        0 :
                        Math.round(channelResult.complete / divider * 100);

                    _.each(channelResult.locales, function (localeResult, locale) {
                        var divider = channelResult.total;
                        var ratio = divider === 0 ?
                            0 :
                            Math.round(localeResult / divider * 100);

                        channelResult.locales[locale] = {
                            complete: localeResult,
                            ratio: ratio
                        };
                    });
                });

                return data;
            }
        });
    }
);

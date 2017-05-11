define(
    ['jquery', 'underscore', 'pim/dashboard/abstract-widget', 'pim/dashboard/template/completeness-widget'],
    function ($, _, AbstractWidget, template) {
        'use strict';

        return AbstractWidget.extend({
            tagName: 'table',

            id: 'completeness-widget',

            options: {
                completeBar: 'AknProgress--apply',
                inCompleteBar: 'AknProgress--warning',
                channelsPerRow: 3
            },

            template: _.template(template),

            _afterLoad: function () {
                AbstractWidget.prototype._afterLoad.apply(this, arguments);
                this.loadMore();
            },

            events: {
                'click .load-more': 'loadMore'
            },

            loadMore: function (e) {
                if (undefined !== e) {
                    e.preventDefault();
                }

                var $nextChannels = $('.completeness-widget .channels:not(:visible)');
                if ($nextChannels.length) {
                    $nextChannels.first().show();
                }

                if ($nextChannels.length <= 1) {
                    $('.completeness-widget .load-more').hide();
                }
            },

            _processResponse: function (data) {
                var channelArray = [];
                _.each(data, function (channelResult, channel) {
                    channelResult.name = channel;
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

                    channelArray.push(channelResult);
                });

                return channelArray;
            }
        });
    }
);

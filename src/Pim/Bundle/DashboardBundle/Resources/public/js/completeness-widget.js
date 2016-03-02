define(
    ['jquery', 'underscore', 'pim/dashboard/abstract-widget'],
    function ($, _, AbstractWidget) {
        'use strict';

        return AbstractWidget.extend({
            tagName: 'table',

            id: 'completeness-widget',

            options: {
                completeBar: 'bar-success',
                inCompleteBar: 'bar-warning'
            },

            template: _.template(
                [
                    '<% _.each(data, function (channelResult, channel) { %>',
                        '<tr class="channel">',
                            '<td>',
                                '<a href="#" data-toggle-channel="<%= channel %>">',
                                    '<i class="icon-caret-down"></i>',
                                '</a>',
                            '</td>',
                            '<td>',
                                '<a href="#" data-toggle-channel="<%= channel %>">',
                                    '<b><%= channel %></b>',
                                '</a>',
                            '</td>',
                            '<td>',
                                '<b><%= channelResult.percentage %>%</b>',
                            '</td>',
                            '<td>&nbsp;</td>',
                        '</tr>',
                        '<% _.each(channelResult.locales, function (localeResult, locale) { %>',
                            '<tr data-channel="<%= channel %>">',
                                '<td>&nbsp;</td>',
                                '<td><%= locale %></td>',
                                '<td><%= localeResult.ratio %>%</td>',
                                '<td class="progress-cell">',
                                    '<div class="progress">',
                                        '<div class="bar ' + '<%= localeResult.ratio === 100 ? ' +
                                            'options.completeBar : options.inCompleteBar %>" ' +
                                            'style="width: <%= localeResult.ratio %>%;"></div>',
                                    '</div>',
                                    '<small><%= localeResult.complete %>/<%= channelResult.total %></small>',
                                '</td>',
                            '</tr>',
                        '<% }); %>',
                    '<% }); %>'
                ].join('')
            ),

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

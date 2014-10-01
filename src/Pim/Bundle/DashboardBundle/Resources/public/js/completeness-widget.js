define(
    ['jquery', 'underscore', 'backbone', 'routing', 'oro/loading-mask', 'oro/mediator', 'oro/navigation'],
    function ($, _, Backbone, Routing, LoadingMask, mediator, Navigation) {
        'use strict';

        var CompletenessWidget = Backbone.View.extend({
            tagName: 'table',

            id: 'completeness-widget',

            options: {
                completeBar: 'bar-success',
                inCompleteBar: 'bar-warning',
                delayedLoadTimeout: 1000,
                minRefreshInterval: 10000
            },

            data: {},

            loadingMask: null,

            $refreshBtn: null,

            loadTimeout: null,

            needsData: true,

            template: _.template(
                [
                    '<% _.each(data, function(channelResult, channel) { %>',
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
                        '<% _.each(channelResult.locales, function(localeResult, locale) { %>',
                            '<tr data-channel="<%= channel %>">',
                                '<td>&nbsp;</td>',
                                '<td><%= locale %></td>',
                                '<td><%= localeResult.ratio %>%</td>',
                                '<td class="progress-cell">',
                                    '<div class="progress">',
                                        '<div class="bar <%= localeResult.ratio === 100 ? options.completeBar : options.inCompleteBar %>" style="width: <%= localeResult.ratio %>%;"></div>',
                                    '</div>',
                                    '<small><%= localeResult.complete %>/<%= channelResult.total %></small>',
                                '</td>',
                            '</tr>',
                        '<% }); %>',
                    '<% }); %>'
                ].join('')
            ),

            refreshBtnTemplate: _.template(
                '<button class="btn btn-mini pull-right"><i class="icon-refresh"></i></button>'
            ),

            events: {
                'click a[data-toggle-channel]': 'toggleChannel'
            },

            initialize: function(options) {
                if (options) {
                    this.options = _.extend(this.options, options);
                }

                mediator.on('hash_navigation_request:complete', function () {
                    if (this.isDashboardPage()) {
                        this.delayedLoad();
                    }
                }, this);
            },

            render: function() {
                this.$el.html(this.template({ data: this.data, options: this.options }));

                return this;
            },

            setElement: function() {
                Backbone.View.prototype.setElement.apply(this, arguments);

                this._createLoadingMask();
                this._createRefreshBtn();

                return this;
            },

            toggleChannel: function(e) {
                e.preventDefault();

                var channel = $(e.currentTarget).data('toggle-channel');
                this.$('tr[data-channel="' + channel + '"]').toggle();
                this.$('a[data-toggle-channel="' + channel + '"] i')
                    .toggleClass('icon-caret-right icon-caret-down');
            },

            isDashboardPage: function() {
                return Navigation.getInstance().url === Routing.generate('oro_default');
            },

            loadData: function() {
                if (!this.needsData || !this.isDashboardPage()) {
                    this.loadTimeout = null;

                    return;
                }
                this.needsData = false;
                this._beforeLoad();

                $.get(Routing.generate('pim_dashboard_widget_data', { alias: 'completeness' }))
                    .then(_.bind(function(resp) {
                        this.data = this._processResponse(resp);
                        this.render();
                        this._afterLoad();
                    }, this));
            },

            reload: function() {
                this.needsData = true;

                this.loadData();
            },

            delayedLoad: function() {
                if (!this.loadTimeout) {
                    this.loadTimeout = setTimeout(_.bind(function() {
                        this.loadData();
                    }, this), this.options.delayedLoadTimeout);
                }
            },

            _beforeLoad: function() {
                this.loadingMask.$el.css('min-height', _.isEmpty(this.data) ? 100 : 0);
                this.$refreshBtn.prop('disabled', true).find('i').addClass('icon-spin');
                this.loadingMask.show();
            },

            _afterLoad: function() {
                this.loadingMask.hide();
                this.$refreshBtn.prop('disabled', false).find('i').removeClass('icon-spin');
                this.loadTimeout = null;
                setTimeout(_.bind(function() {
                    this.needsData = true;
                }, this), this.options.minRefreshInterval);
            },

            _createLoadingMask: function() {
                if (this.loadingMask) {
                    this.loadingMask.remove();
                }
                this.loadingMask = new LoadingMask();
                this.loadingMask.render().$el.insertAfter(this.$el);
            },

            _createRefreshBtn: function() {
                if (this.$refreshBtn) {
                    this.$refreshBtn.remove();
                }

                this.$refreshBtn = $(this.refreshBtnTemplate());
                this.$refreshBtn.on('click', _.bind(this.reload, this));

                this.$el.parent().siblings('.widget-header').append(this.$refreshBtn);
            },

            _processResponse: function(data) {
                _.each(data, function(channelResult) {
                    var divider = channelResult.total * _.keys(channelResult.locales).length;

                    channelResult.percentage = divider === 0 ?
                        0 :
                        Math.round(channelResult.complete / divider * 100);

                    _.each(channelResult.locales, function(localeResult, locale) {
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

        var completenessWidget = null;

        return {
            init: function(options) {
                if (!completenessWidget) {
                    completenessWidget = new CompletenessWidget(options);
                } else if (_.has(options, 'el')) {
                    completenessWidget.setElement(options.el);
                }
                completenessWidget.render().delayedLoad();
            }
        };
    }
);

define(
    [
        'backbone',
        'jquery',
        'underscore',
        'routing',
        'pim/notification-list',
        'pim/indicator',
        'text!pim/template/notification/notification',
        'text!pim/template/notification/notification-footer'
    ],
    function (Backbone, $, _, Routing, NotificationList, Indicator, notificationTpl, notificationFooterTpl) {
        'use strict';

        var Notifications = Backbone.View.extend({
            el: '#header-notification-widget',

            options: {
                imgUrl:                 '',
                loadingText:            null,
                noNotificationsMessage: null,
                markAsReadMessage:      null,
                indicatorBaseClass:     'badge badge-square',
                indicatorEmptyClass:    'hide',
                refreshInterval:        30000
            },

            freezeCount: false,

            refreshTimeout: null,

            refreshLocked: false,

            template: _.template(notificationTpl),

            footerTemplate: _.template(notificationFooterTpl),

            events: {
                'click a.dropdown-toggle':   'onOpen',
                'click button.mark-as-read': 'markAllAsRead'
            },

            markAllAsRead: function (e) {
                e.stopPropagation();
                e.preventDefault();

                $.ajax({
                    type: 'POST',
                    url: Routing.generate('pim_notification_notification_mark_viewed'),
                    async: true
                });

                this.collection.trigger('mark_as_read', null);
                _.each(this.collection.models, function (model) {
                    model.set('viewed', true);
                });
            },

            initialize: function (opts) {
                this.options = _.extend({}, this.options, opts);
                this.collection = new NotificationList();
                this.indicator  = new Indicator({
                    el: this.$('span.indicator'),
                    value: 0,
                    className: this.options.indicatorBaseClass,
                    emptyClass: this.options.indicatorEmptyClass
                });

                this.collection.on('load:unreadCount', function (count, reset) {
                    this.scheduleRefresh();
                    if (this.freezeCount) {
                        this.freezeCount = false;
                        return;
                    }
                    if (this.indicator.get('value') !== count) {
                        this.indicator.set('value', count);
                        if (reset) {
                            this.collection.hasMore = true;
                            this.collection.reset();
                            this.renderFooter();
                        }
                    }
                }, this);

                this.collection.on('mark_as_read', function (id) {
                    var value = null === id ? 0 : this.indicator.get('value') - 1;
                    this.indicator.set('value', value);
                    if (0 === value) {
                        this.renderFooter();
                    }
                    if (null !== id) {
                        this.freezeCount = true;
                    }
                }, this);

                this.collection.on('loading:start loading:finish remove', this.renderFooter, this);

                this.render();

                this.scheduleRefresh();
            },

            scheduleRefresh: function () {
                if (this.refreshLocked) {
                    return;
                }
                if (null !== this.refreshTimeout) {
                    clearTimeout(this.refreshTimeout);
                }

                this.refreshTimeout = setTimeout(_.bind(function () {
                    this.refreshLocked = true;
                    $.getJSON(Routing.generate('pim_notification_notification_count_unread'))
                        .then(_.bind(function (count) {
                            this.refreshLocked = false;
                            this.collection.trigger('load:unreadCount', count, true);
                        }, this));
                }, this), this.options.refreshInterval);
            },

            onOpen: function () {
                if (!this.collection.length) {
                    this.collection.loadNotifications();
                }
            },

            render: function () {
                this.setElement($('#header-notification-widget'));
                this.$el.html(this.template());
                this.collection.setElement(this.$('ul'));
                this.indicator.setElement(this.$('span.indicator'));
                this.renderFooter();
            },

            renderFooter: function () {
                this.$('p').remove();

                this.$('ul').append(
                    this.footerTemplate({
                        options:          this.options,
                        loading:          this.collection.loading,
                        hasNotifications: this.collection.length > 0,
                        hasMore:          this.collection.hasMore,
                        hasUnread:        this.indicator.get('value') > 0
                    })
                );
            }
        });

        var notifications;

        return {
            init: function (options) {
                if (notifications) {
                    notifications.render();
                } else {
                    notifications = new Notifications(options);
                }
                if (_.has(options, 'unreadCount')) {
                    notifications.collection.trigger('load:unreadCount', options.unreadCount, true);
                }
            }
        };
    }
);

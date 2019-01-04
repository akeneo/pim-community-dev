define(
    [
        'backbone',
        'jquery',
        'underscore',
        'routing',
        'pim/notification-list',
        'pim/indicator',
        'pim/template/notification/notification',
        'pim/template/notification/notification-footer'
    ],
    function (Backbone, $, _, Routing, NotificationList, Indicator, notificationTpl, notificationFooterTpl) {
        'use strict';

        const NOTIFICATION_TIMEOUT_ID = 'notifications_timeout_ids';

        return Backbone.View.extend({
            options: {
                imgUrl:                 '',
                loadingText:            null,
                noNotificationsMessage: null,
                markAsReadMessage:      null,
                indicatorBaseClass:     'AknNotificationMenu-count',
                indicatorEmptyClass:    'AknNotificationMenu-count--hidden',
                refreshInterval:        30000
            },

            freezeCount: false,

            template: _.template(notificationTpl),

            footerTemplate: _.template(notificationFooterTpl),

            events: {
                'click .notification-link': 'onOpen',
                'click .mark-as-read': 'markAllAsRead'
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
                    el: this.$('.AknNotificationMenu-countContainer'),
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
                const timeoutId = sessionStorage.getItem(NOTIFICATION_TIMEOUT_ID);
                if (timeoutId !== null && timeoutId !== '') {
                    clearTimeout(parseInt(timeoutId));
                }

                const newTimeoutId = setTimeout(this.refresh.bind(this), this.options.refreshInterval);
                sessionStorage.setItem(NOTIFICATION_TIMEOUT_ID, newTimeoutId + '');
            },

            refresh: function () {
                $.getJSON(Routing.generate('pim_notification_notification_count_unread'))
                    .then(_.bind(function (count) {
                        sessionStorage.setItem('notificationRefreshLocked', 'available');
                        this.collection.trigger('load:unreadCount', count, true);
                    }, this));
            },

            onOpen: function () {
                if (!this.collection.length) {
                    this.collection.loadNotifications();
                }
            },

            render: function () {
                this.$el.html(this.template());
                this.collection.setElement(this.$('ul'));
                this.indicator.setElement(this.$('.AknNotificationMenu-countContainer'));
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
    }
);

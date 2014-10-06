define(
    ['backbone', 'jquery', 'underscore', 'routing', 'pim/notification-list', 'pim/indicator'],
    function(Backbone, $, _, Routing) {
        'use strict';

        var NotificationList = require('pim/notification-list'),
            Indicator = require('pim/indicator');

        var Notifications = Backbone.View.extend({
            el: '#header-notification-widget',

            options: {
                imgUrl:                 '',
                loadingText:            null,
                noNotificationsMessage: null,
                markAsReadMessage:      null,
                indicatorBaseClass:     'badge badge-square',
                indicatorEmptyClass:    'hide'
            },

            freezeCount: false,

            template: _.template(
                [
                    '<a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">',
                        '<i class="icon-bell"></i>',
                        '<span class="indicator"></span>',
                    '</a>',
                    '<ul class="dropdown-menu"></ul>'
                ].join('')
            ),

            footerTemplate: _.template(
                [
                    '<p class="text-center unspaced">',
                        '<% if (loading) { %>',
                            '<img src="<%= options.imgUrl %>" alt="<%= options.loadingText %>" />',
                        '<% } %>',
                        '<% if (!loading && !hasNotifications && !hasMore) { %>',
                            '<span><%= options.noNotificationsMessage %></span>',
                        '<% } %>',
                        '<% if (hasNotifications && hasUnread) { %>',
                            '<button class="btn btn-mini mark-as-read"><%= options.markAsReadMessage %></button>',
                        '<% } %>',
                    '</p>'
                ].join('')
            ),

            events: {
                'click a.dropdown-toggle':   'onOpen',
                'click button.mark-as-read': 'markAllAsRead'
            },

            markAllAsRead: function(e) {
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

            initialize: function(opts) {
                this.options = _.extend({}, this.options, opts);
                this.collection = new NotificationList();
                this.indicator  = new Indicator({
                    el: this.$('span.indicator'),
                    value: 0,
                    className: this.options.indicatorBaseClass,
                    emptyClass: this.options.indicatorEmptyClass
                });

                this.collection.on('load:unreadCount', function(count, reset) {
                    if (this.freezeCount) {
                        this.freezeCount = false;
                        return;
                    }
                    if (this.indicator.get('value') != count) {
                        this.indicator.set('value', count);
                        if (reset) {
                            this.collection.hasMore = true;
                            this.collection.reset();
                            this.renderFooter();
                        }
                    }
                }, this);

                this.collection.on('mark_as_read', function(id) {
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
            },

            onOpen: function() {
                if (!this.collection.length) {
                    this.collection.loadNotifications();
                }
            },

            render: function() {
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
            init: function(options) {
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

define(
    ['backbone', 'jquery', 'underscore', 'routing', 'oro/navigation'],
    function (Backbone, $, _, Routing, Navigation) {
        'use strict';

        var Notification = Backbone.Model.extend({
            defaults: {
                viewed:            false,
                url:               null,
                message:           '',
                id:                null,
                type:              'success',
                createdAt:         null,
                actionType:        null,
                actionTypeMessage: null
            }
        });

        var NotificationList = Backbone.Collection.extend({
            model:     Notification,
            loading:   false,
            hasMore:   true
        });

        var NotificationView = Backbone.View.extend({
            tagName: 'li',
            model: Notification,

            template: _.template(
                [
                    '<a href="<%= url ? url : \'javascript: void(0);\' %>"<%= viewed ? \'\' : \'class="new"\' %>>',
                        '<div class="action_icon <%= actionType %>">',
                            '<i class="status icon-<%= icon %>"></i>',
                        '</div>',
                        '<div class="metas">',
                            '<span class="title"><%= actionTypeMessage %></span>',
                            '<span class="message"><%= message %></span>',
                        '</div>',
                        '<time><%= createdAt %></time>',
                        '<i class="icon-<%= viewed ? \'trash\' : \'eye-close\' %> action"></i>',
                        '<button class="btn icons-holder-text"><i class="icon-file-text-alt"></i>Report</button>',
                    '</a>'
                ].join('')
            ),

            events: {
                'click .icon-trash':     'remove',
                'click .icon-eye-close': 'markAsRead',
                'click i':               'preventOpen',
                'click a.new':           'markAsRead',
                'click a':               'open'
            },

            remove: function () {
                this.model.destroy({
                    url: Routing.generate('pim_notification_notification_remove', { id: this.model.get('id') }),
                    wait: false,
                    _method: 'DELETE'
                });

                this.$el.fadeOut(function () {
                    this.remove();
                });
            },

            open: function (e) {
                this.preventOpen(e);
                if (this.model.get('url')) {
                    Navigation.getInstance().setLocation(this.model.get('url'));
                }
            },

            preventOpen: function (e) {
                e.preventDefault();
                e.stopPropagation();
            },

            markAsRead: function () {
                this.model.trigger('mark_as_read', this.model.id);
                this.model.set('viewed', true);
                $.ajax({
                    type: 'POST',
                    url: Routing.generate('pim_notification_notification_mark_viewed', {id: this.model.id}),
                    async: true
                });
            },

            initialize: function () {
                this.listenTo(this.model, 'change', this.render);

                this.render();
            },

            render: function () {
                this.$el.html(
                    this.template({
                        viewed: this.model.get('viewed'),
                        message: this.model.get('message'),
                        url: this.model.get('url'),
                        icon: this.getIcon(this.model.get('type')),
                        createdAt: this.model.get('createdAt'),
                        actionType: this.model.get('actionType'),
                        actionTypeMessage: this.model.get('actionTypeMessage')
                    }
                ));

                return this;
            },

            getIcon: function (type) {
                return 'success' === type ? 'ok' : ('warning' === type ? 'warning-sign' : 'remove');
            }
        });

        var NotificationListView = Backbone.View.extend({
            tagName: 'ol',
            className: 'scroll-menu',

            collection: NotificationList,

            events: {
                'scroll': 'onScroll'
            },

            initialize: function () {
                _.bindAll(this, 'render');

                this.collection.on('add reset', this.render);

                this.render();
            },

            onScroll: function () {
                var self = this;
                this.$el.on('scroll', function () {
                    if ($(this).scrollTop() + $(this).innerHeight() >= this.scrollHeight) {
                        self.loadNotifications();
                    }
                });
            },

            loadNotifications: function () {
                if (this.collection.loading || !this.collection.hasMore) {
                    return;
                }

                this.collection.loading = true;

                this.collection.trigger('loading:start');

                $.getJSON(Routing.generate('pim_notification_notification_list') + '?skip=' + this.collection.length)
                    .then(_.bind(function (data) {
                        this.collection.add(data.notifications);
                        this.collection.hasMore = data.notifications.length >= 10;

                        this.collection.trigger('load:unreadCount', data.unreadCount);
                        this.collection.loading = false;
                        this.collection.trigger('loading:finish');
                    }, this));
            },

            render: function () {
                this.$el.empty();

                _.each(this.collection.models, function (model) {
                    this.renderNotification(model);
                }, this);
            },

            renderNotification: function (item) {
                var itemView = new NotificationView({
                    model: item
                });

                this.$el.append(itemView.$el);
            }
        });

        return function (opts) {
            var notificationList = new NotificationList();
            var options = _.extend({}, { el: null, collection: notificationList }, opts);
            var notificationListView = new NotificationListView(options);

            notificationList.setElement = function (element) {
                notificationListView.$el.prependTo(element);
                notificationListView.delegateEvents();
                notificationListView.render();
            };
            notificationList.loadNotifications = function () {
                return notificationListView.loadNotifications();
            };

            return notificationList;
        };
    }
);

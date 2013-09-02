/* global define */
define(['jquery', 'underscore', 'backbone'],
function($, _, Backbone) {
    'use strict';

    var Model = Backbone.Model.extend({
            defaults: {
                type: false,
                message: ''
            }
        }),

        View = Backbone.View.extend({
            options: {
                el: '#flash-messages',
                frameEl: '.flash-messages-frame',
                messagesHolder: '.flash-messages-holder',
                delay: false
            },

            template: _.template(
                '<div class="alert <% if (type) { %><%= "alert-" + type %><% } %> fade in top-messages ">' +
                    '<a class="close" href="#">&times;</a>' +
                    '<div class="message"><%= message %></div>' +
                '</div>'
            ),

            initialize: function(options)
            {
                this.listenTo(this.model, 'destroy', this.remove);
                this.message = $(this.template(this.model.toJSON()));
                this.message.find('.close').on('click', _.bind(this.close, this));

                this.$frame = this.$el.find(this.options.frameEl);
                this.$container = this.$frame.find(this.options.messagesHolder);

                this.render();
            },

            close: function(e)
            {
                this.message.hide(500, _.bind(function () {
                    this.model.destroy();
                    if (!this.$container.children().length) {
                        this.$frame.hide();
                    }
                }, this));
                if (e !== undefined) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            },

            remove: function() {
                this.message.remove();
            },

            render: function ()
            {
                this.$container.append(this.message);
                this.$frame.show();

                if (this.options.delay) {
                    _.delay(_.bind(this.close, this), this.options.delay);
                }

                return this;
            }
        });

    /**
     * @export oro/messenger
     * @name   oro.messenger
     */
    return {
        notificationMessage: function(type, message, options) {
            options = options || {};
            options.model = new Model({
                'type': type,
                'message': message
            });
            return new View(options);
        },

        notificationFlashMessage: function(type, message, delay, options) {
            options = options || {};
            options.delay = (delay !== undefined && delay) ? delay : 5000;
            options.model = new Model({
                'type': type,
                'message': message
            });
            return new View(options);
        }
    };
});

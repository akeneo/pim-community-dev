/* jshint browser:true */
/* global define */
define(['jquery', 'underscore', 'backbone', 'oro/app', 'oro/navigation', 'oro/mediator', 'oro/error'],
function($, _, Backbone, app, Navigation, mediator, error) {
    'use strict';

    /**
     * @export  oro/navigation/pinbar/item-view
     * @class   oro.navigation.pinbar.ItemView
     * @extends Backbone.View
     */
    return Backbone.View.extend({

        options: {
            type: 'list'
        },

        tagName:  'li',

        isRemoved: false,

        templates: {
            list: _.template($("#template-list-pin-item").html()),
            tab: _.template($("#template-tab-pin-item").html())
        },

        events: {
            'click .btn-close': 'unpin',
            'click .close': 'unpin',
            'click .pin-holder div a': 'maximize',
            'click span': 'maximize'
        },

        initialize: function() {
            this.listenTo(this.model, 'destroy', this.removeItem);
            this.listenTo(this.model, 'change:display_type', this.removeItem);
            this.listenTo(this.model, 'change:remove', this.unpin);
            /**
             * Change active pinbar item after hash navigation request is completed
             */
            mediator.bind(
                "hash_navigation_request:complete",
                function() {
                    /*if (!this.isRemoved && this.checkCurrentUrl()) {
                        this.maximize();
                    }*/
                    this.setActiveItem();
                },
                this
            );
        },

        unpin: function() {
            mediator.trigger("pinbar_item_remove_before", this.model);
            this.model.destroy({
                wait: true,
                error: _.bind(function(model, xhr, options) {
                    if (xhr.status == 404 && !app.debug) {
                        // Suppress error if it's 404 response and not debug mode
                        this.removeItem();
                    } else {
                        error.dispatch(model, xhr, options);
                    }
                }, this)
            });
            return false;
        },

        maximize: function() {
            this.model.set('maximized', new Date().toISOString());
            return false;
        },

        removeItem: function() {
            this.isRemoved = true;
            this.remove();
        },

        checkCurrentUrl: function() {
            var url = '',
                modelUrl = this.model.get('url'),
                navigation = Navigation.getInstance();
            if (navigation) {
                url = navigation.getHashUrl();
                url = navigation.removeGridParams(url);
                modelUrl = navigation.removeGridParams(modelUrl);
            } else {
                url = window.location.pathname;
            }
            return modelUrl == url;
        },

        setActiveItem: function() {
            if (this.checkCurrentUrl()) {
                this.$el.addClass('active');
            } else {
                this.$el.removeClass('active');
            }
        },

        render: function () {
            this.$el.html(
                this.templates[this.options.type](this.model.toJSON())
            );
            this.setActiveItem();
            return this;
        }
    });
});

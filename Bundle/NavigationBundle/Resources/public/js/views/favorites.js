var navigation = navigation || {};
navigation.favorites = navigation.favorites || {};

navigation.favorites.MainView = navigation.MainViewAbstract.extend({
    options: {
        el: '.favorite-button',
        tabTitle: 'Favorites',
        tabIcon: 'icon-star-empty',
        tabId: 'favorite'
    },

    events: {
        'click': 'toggleItem'
    },

    initialize: function() {
        this.options.collection = new navigation.ItemsList();

        this.listenTo(this.getCollection(), 'add', this.addItemToTab);
        this.listenTo(this.getCollection(), 'reset', this.addAll);
        this.listenTo(this.getCollection(), 'all', this.render);

        this.$icon = this.$('i');

        this.registerTab();
        this.cleanupTab();
        /**
         * Render links in favorites menu after hash navigation request is completed
         */
        Oro.Events.bind(
            "hash_navigation_request:complete",
            function() {
                this.render();
            },
            this
        );
    },

    activate: function() {
        this.$icon.addClass('icon-gold');
    },

    inactivate: function() {
        this.$icon.removeClass('icon-gold');
    },

    toggleItem: function(e) {
        var self = this;
        var current = this.getItemForCurrentPage();
        if (current.length) {
            _.each(current, function(item) {
                item.destroy({
                    wait: false, // This option affects correct disabling of favorites icon
                    error: function(model, xhr, options) {
                        if (xhr.status == 404 && !Oro.debug) {
                            // Suppress error if it's 404 response and not debug mode
                            self.inactivate();
                        } else {
                            Oro.BackboneError.Dispatch(model, xhr, options);
                        }
                    }
                });
            });
        } else {
            var itemData = this.getNewItemData(Backbone.$(e.currentTarget));
            itemData['type'] = 'favorite';
            itemData['position'] = this.getCollection().length;

            var currentItem = new navigation.Item(itemData);
            this.getCollection().unshift(currentItem);
            currentItem.save();
        }
    },

    addAll: function(items) {
        items.each(function(item) {
            this.addItemToTab(item);
        }, this);
    },

    render: function() {
        this.checkTabContent();
        if (this.getItemForCurrentPage().length) {
            this.activate();
        } else {
            this.inactivate();
        }
        /**
         * Backbone event. Fired when tab is changed
         * @event tab:changed
         */
        Oro.Events.trigger("tab:changed", this.options.tabId);
        return this;
    }
});

/* jshint browser:true */
/* global define */
define(['jquery', 'underscore', 'backbone', 'oro/navigation', 'oro/mediator', 'oro/navigation/abstract-view',
    'oro/navigation/pinbar/item-view', 'oro/navigation/pinbar/collection', 'oro/navigation/pinbar/model'],
function($, _, Backbone, Navigation, mediator, AbstractView,
    PinbarItemView, PinbarCollection, PinbarModel) {
    'use strict';

    /**
     * @export  oro/navigation/pinbar/view
     * @class   oro.navigation.pinbar.View
     * @extends oro.navigation.AbstractView
     */
    return AbstractView.extend({
        options: {
            maxItems: 10,
            tabTitle: 'Pinbar',
            tabIcon: 'icon-folder-close',
            el: '.pin-bar',
            listBar: '.list-bar',
            minimizeButton: '.top-action-box .minimize-button',
            defaultUrl: '/',
            tabId: 'pinbar',
            collection: null
        },

        requireCleanup: true,
        massAdd: false,

        templates: {
            noItemsMessage: _.template($("#template-no-pins-message").html())
        },

        initialize: function() {
            AbstractView.prototype.initialize.apply(this, arguments);
            this.$listBar = this.getBackboneElement(this.options.listBar);
            this.$minimizeButton = Backbone.$(this.options.minimizeButton);
            this.$icon = this.$minimizeButton.find('i');

            if (!this.options.collection) {
                this.options.collection = new PinbarCollection();
            }

            this.listenTo(this.options.collection, 'add', function(item) {this.setItemPosition(item)});
            this.listenTo(this.options.collection, 'remove', this.onPageClose);
            this.listenTo(this.options.collection, 'reset', this.addAll);
            this.listenTo(this.options.collection, 'all', this.render);

            this.listenTo(this.options.collection, 'positionChange', this.renderItem);
            this.listenTo(this.options.collection, 'stateChange', this.handleItemStateChange);
            this.listenTo(this.options.collection, 'urlChange', this.renderItem);

            /**
             * Changing pinbar state after grid is loaded
             */
            mediator.bind(
                "grid_load:complete",
                this.updatePinbarState,
                this
            );

            /**
             * Change pinbar icon state after hash navigation request is completed
             */
            mediator.bind(
                "hash_navigation_request:complete",
                this.checkPinbarIcon,
                this
            );

            this.$minimizeButton.click(_.bind(this.changePageState, this));

            this.registerTab();
            this.cleanup();
            this.render();
        },

        resetCollection: function() {
            this.options.collection.reset.apply(this.options.collection, arguments);
        },

        /**
         * Get backbone DOM element
         *
         * @param el
         * @return {*}
         */
        getBackboneElement: function(el) {
            return el instanceof Backbone.$ ? el : this.$(el);
        },

        /**
         * Handle item minimize/maximize state change
         *
         * @param item
         */
        handleItemStateChange: function(item) {
            if (!this.massAdd) {
                var url = null,
                    navigation,
                    changeLocation = item.get('maximized');
                if (changeLocation) {
                    url = item.get('url');
                }
                if (this.cleanupUrl(url) != this.cleanupUrl(this.getCurrentPageItemData().url)) {
                    navigation = Navigation.getInstance();
                    if (navigation && changeLocation) {
                        navigation.setLocation(url, {useCache: true});
                    }
                    item.save(
                        null,
                        {
                            wait: true,
                            success: _.bind(function () {
                                this.checkPinbarIcon();
                                if (!navigation && changeLocation) {
                                    window.location.href = url;
                                }
                            }, this)
                        }
                    );
                }
            }
        },

        checkPinbarIcon: function() {
            if (this.getItemForCurrentPage().length) {
                this.activate();
            } else {
                this.inactivate();
            }
        },

        /**
         * Handle page close
         */
        onPageClose: function(item) {
            this.checkPinbarIcon();
            this.reorder();
        },

        /**
         * Handle minimize/maximize page.
         *
         * @param e
         */
        changePageState: function(e) {
            var item = this.getItemForCurrentPage(true);
            if (item.length) {
                this.closePage(item);
            } else {
                this.minimizePage(e);
            }
        },

        /**
         * Handle minimize page.
         *
         * @param e
         */
        minimizePage: function(e) {
            mediator.trigger('pinbar_item_minimized');
            this.updatePinbarState();
            var pinnedItem = this.getItemForCurrentPage(true);
            if (pinnedItem.length) {
                _.each(pinnedItem, function(item) {
                    item.set('maximized', false);
                }, this);
            } else {
                var newItem = this.getNewItemData(Backbone.$(e.currentTarget));
                newItem.url = this.cleanupUrl(newItem.url);
                var currentItem = new PinbarModel(newItem);
                this.options.collection.unshift(currentItem);
                this.handleItemStateChange(currentItem);
            }
        },

        /**
         *  Update current page item state to use new url
         */
        updatePinbarState: function() {
            var pinnedItem, hashUrl,
                navigation = Navigation.getInstance();
            if (navigation && navigation.useCache) {
                pinnedItem = this.getItemForCurrentPage(true);
                if (pinnedItem.length) {
                     hashUrl = navigation.getHashUrl(true, true);
                     _.each(pinnedItem, function(item) {
                         if (item.get('url') !== hashUrl) {
                             item.set('url', hashUrl);
                             item.save();
                         }
                     }, this);
                }
            }
        },

        /**
         * Handle pinbar close
         *
         * @param item
         */
        closePage: function(item) {
            _.each(item, function(item) {
                item.set('remove', true);
            });
        },

        /**
         * Mass add items
         */
        addAll: function() {
            this.massAdd = true;
            this.markCurrentPageMaximized();
            this.options.collection.each(this.setItemPosition, this);
            this.massAdd = false;
        },

        /**
         * Mark current page as maximized to be able to minimize.
         */
        markCurrentPageMaximized: function()
        {
            var currentPageItems = this.getItemForCurrentPage(true);
            if (currentPageItems.length) {
                _.each(currentPageItems, function(item) {
                    item.set('maximized', new Date().toISOString());
                });
            }
        },

        /**
         * Set item position if given or reorder items.
         *
         * @param {oro.navigation.pinbar.Model} item
         * @param {number} position
         */
        setItemPosition: function(item, position) {
            if (_.isUndefined(position)) {
                this.reorder();
            } else {
                item.set({position: position});
            }
        },

        /**
         * Change position property of model based on current order
         */
        reorder: function() {
            this.options.collection.each(function(item, position) {
                item.set({position: position});
            });
        },

        activate: function() {
            this.$icon.addClass('icon-gold');
        },

        inactivate: function() {
            this.$icon.removeClass('icon-gold');
        },

        /**
         * Choose container and add item to it.
         *
         * @param {oro.navigation.pinbar.Model} item
         */
        renderItem: function(item) {
            var position = item.get('position');
            var type = position >= this.options.maxItems ? 'tab': 'list';

            if (item.get('display_type') != type) {
                this.cleanup();
                item.set('display_type', type);

                var view = new PinbarItemView({
                    type: type,
                    model: item
                });

                if (type == 'tab') {
                    this.addItemToTab(view, !this.massAdd);
                    /**
                     * Backbone event. Fired when tab is changed
                     * @event tab:changed
                     */
                    mediator.trigger("tab:changed", this.options.tabId);
                } else {
                    var rowEl = view.render().el;
                    if (this.massAdd || position > 0) {
                        this.$listBar.append(rowEl);
                    } else {
                        this.$listBar.prepend(rowEl);
                    }
                }
            }
        },

        /**
         * Checks if pinbar tab in 3 dots menu is used
         *
         * @return {Boolean}
         */
        needPinbarTab: function() {
            return (this.options.collection.length > this.options.maxItems);
        },

        /**
         * Clean up all pinbar items from menus
         */
        cleanup: function()
        {
            if (this.requireCleanup) {
                this.$listBar.empty();
                this.cleanupTab();
                this.requireCleanup = false;
            }
        },

        /**
         * Renders pinbar empty message if no items
         * Show/hide tabs section in ... menu on each event
         */
        render: function() {
            if (!this.massAdd) {
                if (this.options.collection.length == 0) {
                    this.requireCleanup = true;
                    this.$listBar.html(this.templates.noItemsMessage());
                    /**
                     * Backbone event. Fired when pinbar help link is shown
                     * @event pinbar_help:shown
                     */
                    mediator.trigger("pinbar_help:shown");
                }

                this.checkTabContent();
                /**
                 * Backbone event. Fired when tab is changed
                 * @event tab:changed
                 */
                mediator.trigger("tab:changed", this.options.tabId);
            }
        }
    });
});

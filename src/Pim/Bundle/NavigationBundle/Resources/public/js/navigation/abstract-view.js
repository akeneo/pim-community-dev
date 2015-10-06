/* jshint browser:true */
/* global define */
define(['underscore', 'backbone', 'oro/navigation/dotmenu/view', 'pim/router'],
function(_, Backbone, DotmenuView, router) {
    'use strict';

    /**
     * @export  oro/navigation/abstract-view
     * @class   oro.navigation.AbstractView
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        options: {
            tabTitle: 'Tabs',
            tabIcon: 'icon-folder-close',
            tabId: 'tabs',
            hideTabOnEmpty: false,
            collection: null
        },

        initialize: function() {
            this.dotMenu = new DotmenuView();
        },

        getCollection: function() {
            return this.options.collection;
        },

        registerTab: function() {
            this.dotMenu.addTab({
                key: this.options.tabId,
                title: this.options.tabTitle,
                icon: this.options.tabIcon,
                hideOnEmpty: this.options.hideTabOnEmpty
            });
        },

        /**
         * Search for pinbar items for current page.
         * @param  {Boolean} excludeGridParams
         * @param  {String}  url
         * @return {*}
         */
        getItemForCurrentPage: function(excludeGridParams) {
            return this.getItemForPage(this.getCurrentPageItemData().url, excludeGridParams);
        },

        /**
         * Search for pinbar items for url.
         * @param  {String}  url
         * @param  {Boolean} excludeGridParams
         * @return {*}
         */
        getItemForPage: function(url, excludeGridParams) {
            return this.options.collection.filter(_.bind(function (item) {
                var itemUrl = item.get('url');
                if (!_.isUndefined(excludeGridParams) && excludeGridParams) {
                    itemUrl = itemUrl.split('#g')[0];
                    url = url.split('#g')[0];
                }
                return itemUrl == url;
            }, this));
        },

        /**
         * Get object with info about current page
         * @return {Object}
         */
        getCurrentPageItemData: function() {
            return { url: Backbone.history.getFragment() };
        },

        /**
         * Get data for new navigation item based on element options
         *
         * @param el
         * @returns {Object}
         */
        getNewItemData: function(el) {
            var itemData = this.getCurrentPageItemData();
            itemData['title'] = document.title;
            return itemData;
        },

        cleanupTab: function() {
            this.dotMenu.cleanup(this.options.tabId);
            this.dotMenu.hideTab(this.options.tabId);
        },

        addItemToTab: function(item, prepend) {
            this.dotMenu.addTabItem(this.options.tabId, item, prepend);
        },

        checkTabContent: function() {
            this.dotMenu.checkTabContent(this.options.tabId);
        },

        render: function() {
            this.checkTabContent();
            return this;
        }
    });
});

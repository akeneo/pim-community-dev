var navigation = navigation || {};

navigation.MainViewAbstract = Backbone.View.extend({
    options: {
        tabTitle: 'Tabs',
        tabIcon: 'icon-folder-close',
        tabId: 'tabs',
        hideTabOnEmpty: false,
        collection: null
    },

    getCollection: function() {
        return this.options.collection;
    },

    registerTab: function() {
        navigation.dotMenu.MainViewInstance.addTab({
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
        url = this.cleanupUrl(url);
        return this.options.collection.filter(_.bind(function (item) {
            var itemUrl = this.cleanupUrl(item.get('url'));
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
        var url = '';
        if (Oro.hashNavigationEnabled()) {
            url = Oro.hashNavigationInstance.getHashUrl(true, true);
        } else {
            url = window.location.pathname + window.location.search + window.location.hash;
        }
        return {url: url};
    },

    cleanupUrl: function(url) {
        if (url) {
            url = url.replace(/(\?|&)restore=1/ig, '');
        }
        return url;
    },

    /**
     * Get data for new navigation item based on element options
     *
     * @param el
     * @returns {Object}
     */
    getNewItemData: function(el) {
        var itemData = this.getCurrentPageItemData();
        if (el.data('url')) {
            itemData['url'] = el.data('url');
        }
        itemData['title_rendered'] = el.data('title_rendered') ? el.data('title_rendered') : document.title;
        itemData['title'] = el.data('title') ? JSON.stringify(el.data('title')) : '{"template": "' + document.title + '"}';
        return itemData;
    },

    cleanupTab: function() {
        navigation.dotMenu.MainViewInstance.cleanup(this.options.tabId);
        navigation.dotMenu.MainViewInstance.hideTab(this.options.tabId);
    },

    addItemToTab: function(item, prepend) {
        navigation.dotMenu.MainViewInstance.addTabItem(this.options.tabId, item, prepend);
    },

    checkTabContent: function() {
        navigation.dotMenu.MainViewInstance.checkTabContent(this.options.tabId);
    },

    render: function() {
        this.checkTabContent();
        return this;
    }
});

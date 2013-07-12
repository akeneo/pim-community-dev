var Oro = Oro || {};
/**
 * Router for hash navigation
 *
 * @class   Oro.Navigation
 * @extends Oro.Router
 */
Oro.Navigation = Backbone.Router.extend({

    /**
     * Hash navigation enabled/disabled flag
     */
    enabled: true,

    /**
     * links - Selector for all links that will be processed by hash navigation
     * scrollLinks - Selector for anchor links
     * forms - Selector for all forms that will be processed by hash navigation
     * content - Selector for ajax response content area
     * container - Selector for main content area
     * loadingMask - Selector for loading spinner
     * searchDropdown - Selector for dropdown with search results
     * menuDropdowns - Selector for 3 dots menu and user dropdowns
     * pinbarHelp - Selector for pinbars help link
     * historyTab - Selector for history 3 dots menu tab
     * mostViwedTab - Selector for most viewed 3 dots menu tab
     * flashMessages - Selector for system messages block
     * menu - Selector for system main menu
     * pinButton - Selector for pin, close and favorite buttons div
     *
     * @property
     */
    selectors: {
        links:          'a:not([href^=#],[href^=javascript]),span[data-url]',
        scrollLinks:    'a[href^=#]',
        forms:          'form',
        content:        '#content',
        container:      '#container',
        loadingMask:    '.hash-loading-mask',
        searchDropdown: '#search-div',
        menuDropdowns:  '.pin-menus.dropdown, .nav .dropdown',
        pinbarHelp:     '.pin-bar-empty',
        historyTab:     '#history-content',
        mostViewedTab:  '#mostviewed-content',
        flashMessages:  '#flash-messages',
        menu:           '#main-menu',
        pinButton:      '#pin-button-div'
    },
    selectorCached: {},

    /** @property {Oro.LoadingMask} */
    loadingMask: '',

    /** @property {String} */
    baseUrl: '',

    /**
     * State data for grids
     *
     * @property
     */
    encodedStateData: '',

    /**
     * Url part
     *
     * @property
     */
    url: '',

    /** @property {Oro.DatagridRouter} */
    gridRoute: '',

    /** @property */
    routes: {
        "(url=*page)(|g/*encodedStateData)": "defaultAction",
        "g/*encodedStateData": "gridChangeStateAction"
    },

    /**
     * Flag whether to use states cache for current page load
     */
    useCache: false,

    skipAjaxCall: false,

    maxCachedPages: 10,

    contentCache: [],

    contentCacheUrls: [],

    tempCache: '',

    /**
     * Routing default action
     *
     * @param {String} page
     * @param {String} encodedStateData
     */
    defaultAction: function(page, encodedStateData) {
        this.encodedStateData = encodedStateData;
        this.url = page;
        if (!this.url) {
            this.url = window.location.href.replace(this.baseUrl, '');
        }
        if (!this.skipAjaxCall) {
            this.loadPage();
        }
        this.skipAjaxCall = false;
    },

    /**
     * Routing grid state changed action
     *
     * @param encodedStateData
     */
    gridChangeStateAction: function(encodedStateData) {
        this.encodedStateData = encodedStateData;
    },

    /**
     *  Changing state for grid
     */
    gridChangeState: function() {
        if (!this.getCachedData() && this.gridRoute && this.encodedStateData && this.encodedStateData.length) {
            this.gridRoute.changeState(this.encodedStateData);
        }
    },

    /**
     * Initialize hash navigation
     *
     * @param options
     */
    initialize: function(options) {
        for (var selector in this.selectors) if (this.selectors.hasOwnProperty(selector)) {
            this.selectorCached[selector] = $(this.selectors[selector]);
        }

        options = options || {};
        if (!options.baseUrl) {
            throw new TypeError("'baseUrl' is required");
        }

        this.baseUrl = options.baseUrl;
        if (window.location.hash === '') {
            //skip ajax page refresh for the current page
            this.skipAjaxCall = true;
        }

        this.init();

        Backbone.Router.prototype.initialize.apply(this, arguments);
    },

    /**
     * Ajax call for loading page content
     */
    loadPage: function() {
        if (this.url) {
            this.beforeRequest();
            var cacheData;
            if (cacheData = this.getCachedData()) {
                this.tempCache = cacheData;
                this.handleResponse(cacheData, {fromCache: true});
                this.restoreFormState(cacheData);
                this.validatePageCache(cacheData);
                this.afterRequest();
            } else {
                var pageUrl = this.baseUrl + this.url;
                var useCache = this.useCache;
                $.ajax({
                    url: pageUrl,
                    headers: { 'x-oro-hash-navigation': true },
                    beforeSend: function( xhr ) {
                        //remove standard ajax header because we already have a custom header sent
                        xhr.setRequestHeader('X-Requested-With', {toString: function(){ return ''; }});
                    },

                    error: _.bind(function (jqXHR, textStatus, errorThrown) {
                        this.showError('Error Message: ' + textStatus, 'HTTP Error: ' + errorThrown);
                        this.updateDebugToolbar(jqXHR);
                        this.afterRequest();
                    }, this),

                    success: _.bind(function (data, textStatus, jqXHR) {
                        data = this.getCorrectedData(data);
                        if (!cacheData) {
                            this.handleResponse(data);
                            this.updateDebugToolbar(jqXHR);
                            this.savePageToCache(data);
                            this.afterRequest();
                        }
                        if (useCache) {
                            this.addCurrentPageToCache();
                        }
                    }, this)
                });
            }
        }
    },

    restoreFormState: function(cacheData) {
        if (cacheData.states) {
            var formState = cacheData.states.getObjectCache('form');
            if (formState['form_data'].length) {
                Oro.pagestate.model.setData(formState['form_data']);
                Oro.pagestate.restore();
                Oro.pagestate.needServerRestore = false;
            }
        }
    },

    validateMd5Request: function(cacheData) {
        var pageUrl = this.baseUrl + this.url;
        $.ajax({
            url: pageUrl,
            data:{'hash-navigation-md5' : true, 'x-oro-hash-navigation' : true},
            error: _.bind(function (jqXHR, textStatus, errorThrown) {
            }, this),

            success: _.bind(function (data, textStatus, jqXHR) {
                data = this.getCorrectedData(data);
                this.checkContentMd5(data, cacheData);
            }, this)
        });
    },

    validatePageCache: function(cacheData) {
        var isValid = true;
        if (cacheData.states) {
            var girdState = cacheData.states.getObjectCache('grid');
            if (girdState['collection']) {
                var collection = girdState['collection'].clone();
                var options = {ignoreSaveStateInUrl: true};
                /**
                 * Comparing cached collection with fetched from server
                 */
                options.success = _.bind(function () {
                    if (!_.isEqual(girdState['collection'].toJSON(),collection.toJSON())) {
                        this.showOutdatedMessage();
                    }
                }, this);
                collection.fetch(options);
            }
        }
        this.validateMd5Request(cacheData);
    },

    showOutdatedMessage: function() {
        Oro.NotificationMessage('warning', "Content of the page is outdated, please <span class='page-refresh'>click here</span> to refresh the page");
    },

    checkContentMd5: function(data, cacheData) {
        if (data.content_md5 !== cacheData.content_md5) {
            this.showOutdatedMessage();
        }
    },

    /**
     * Update debug toolbar.
     *
     * @param jqXHR
     */
    updateDebugToolbar: function(jqXHR) {
        var debugBarToken = jqXHR.getResponseHeader('x-debug-token');
        var entryPoint = window.location.pathname;
        if (entryPoint.indexOf('.php') !== -1) {
            entryPoint = entryPoint.substr(0, entryPoint.indexOf('.php') + 4);
        }
        if(debugBarToken) {
            var url = entryPoint + '/_wdt/' + debugBarToken;
            $.get(
                this.baseUrl + url,
                _.bind(function(data) {
                    var dtContainer = $('<div class="sf-toolbar" id="sfwdt' + debugBarToken + '" style="display: block;" data-sfurl="' + url + '"/>');
                    dtContainer.html(data);
                    var scrollable = $('.scrollable-container:last');
                    var container = scrollable.length ? scrollable : this.selectorCached['container'];
                    $('.sf-toolbar').remove();
                    container.append(dtContainer);
                }, this)
            );
        }
    },

    /**
     * Save page content to temp cache
     *
     * @param data
     */
    savePageToCache: function(data) {
        this.tempCache = {};
        this.tempCache = _.clone(data);
        this.tempCache.states = Oro.deepClone(Oro.pageCacheStates);
    },

    /**
     * Add current page to permanent cache
     */
    addCurrentPageToCache: function() {
        var url = this.getHashUrl();
        this.clearPageCache(this.removePageStateParam(url));
        this.contentCacheUrls.push(this.removePageStateParam(url));
        this.contentCache[this.contentCacheUrls.length - 1] = this.tempCache;
    },

    /**
     * Get cache data for url
     * @param url
     * @return {*}
     */
    getCachedData: function(url) {
        if (this.useCache) {
            if (_.isUndefined(url)) {
                url = this.getHashUrl();
            }
            var i;
            if ((i = _.indexOf(this.contentCacheUrls, this.removePageStateParam(url))) !== -1) {
                if (this.contentCache[i]) {
                    return this.contentCache[i];
                }
            }
        }
        return false;
    },

    /**
     * Save page content to cache
     *
     * @param objectName
     * @param state
     * @param url
     */
    updateCachedContent: function(objectName, state) {
        this.tempCache.states.saveObjectCache(objectName, state);
    },

    /**
     * Reorder cache history to put current page to the end
     *
     * @param pos
     */
    reorderCache: function(pos) {
        var tempUrl = this.contentCacheUrls[pos];
        var tempContent = this.contentCache[pos];
        for (var i = pos + 1; i < this.contentCacheUrls.length; i++) {
            this.contentCacheUrls[i - 1] = this.contentCacheUrls[i];
        }
        this.contentCacheUrls[this.contentCacheUrls.length - 1] = tempUrl;
        for (i = pos + 1; i < this.contentCache.length; i++) {
            this.contentCache[i - 1] = this.contentCache[i];
        }
        this.contentCache[this.contentCacheUrls.length - 1] = tempContent;
    },

    /**
     * Clear cache data
     *
     * @param url
     */
    clearPageCache: function(url) {
        if (!_.isUndefined(url)) {
            url = this.removePageStateParam(url);
            var j = _.indexOf(this.contentCacheUrls, url);
            if (j !== -1) {
                this.contentCacheUrls.splice(j, 1);
                this.contentCache.splice(j, 1);
            }
        } else {
            this.contentCacheUrls = [];
            this.contentCache = [];
        }
    },

    /**
     * Remove restore params from url
     *
     * @param url
     * @return {String|XML|void}
     */
    removePageStateParam: function(url) {
        return url.replace(/[\?&]restore=1/g,'');
    },

    /**
     * Init
     */
    init: function() {
        /**
         * Processing all links
         */
        this.processClicks(this.selectorCached.links);
        /**
         * Processing all links in grid after grid load
         */
        Oro.Events.bind(
            "grid_load:complete",
            function (collection) {
                this.updateCachedContent('grid', {'collection': collection});
                var pinbarView = Oro.Registry.getElement('pinbar_view');
                if (pinbarView) {
                    var item = pinbarView.getItemForCurrentPage(true);
                    if (item.length && this.useCache) {
                        this.addCurrentPageToCache();
                    }
                }
                this.processClicks($('.grid-container').find(this.selectors.links));
            },
            this
        );

        /**
         * Loading grid collection from cache
         */
        Oro.Events.bind(
            "datagrid_collection_set_after",
            function (datagridCollection) {
                var data = this.getCachedData();
                if (data.states) {
                    var girdState = data.states.getObjectCache('grid');
                    if (girdState['collection']) {
                        datagridCollection.collection = girdState['collection'].clone();
                    } else {
                        girdState['collection'] = datagridCollection.collection;
                    }
                }
            },
            this
        );

        /**
         * Trigger updateState event for grid collection if page was loaded from cache
         */
        Oro.Events.bind(
            "datagrid_filters:rendered",
            function (collection) {
                if (this.getCachedData()) {
                    collection.trigger('updateState', collection);
                }
            },
            this
        );

        /**
         * Clear page cache for unpinned page
         */
        Oro.Events.bind(
            "pinbar_item_remove_before",
            function (item) {
                var url = this.removeGridParams(item.get('url'));
                this.clearPageCache(url);
            },
            this
        );

        /**
         * Add "pinned" page to cache
         */
        Oro.Events.bind(
            "pinbar_item_minimized",
            function () {
                this.useCache = true;
                var data = this.tempCache;
                if (data.states) {
                    var formState = data.states.getObjectCache('form');
                    formState['form_data'] = Oro.pagestate.model.get('pagestate').data;
                }
                this.addCurrentPageToCache();
            },
            this
        );

        /**
         * Processing navigate action execute
         */
        Oro.Events.bind(
            "grid_action:navigateAction:preExecute",
            function (action, options) {
                this.setLocation(action.getLink());

                options.doExecute = false;
            },
            this
        );

        /**
         * Checking for grid route and updating it's state
         */
        Oro.Events.bind(
            "grid_route:loaded",
            function (route) {
                this.gridRoute = route;
                this.gridChangeState();
            },
            this
        );

        /**
         * Processing links in 3 dots menu after item is added (e.g. favourites)
         */
        Oro.Events.bind(
            "navigaion_item:added",
            function (item) {
                this.processClicks(item.find(this.selectors.links));
            },
            this
        );

        /**
         * Processing links in search result dropdown
         */
        Oro.Events.bind(
            "top_search_request:complete",
            function () {
                this.processClicks($(this.selectorCached.searchDropdown).find(this.selectors.links));
            },
            this
        );

        /**
         * Processing pinbar help link
         */
        Oro.Events.bind(
            "pinbar_help:shown",
            function () {
                this.processClicks(this.selectors.pinbarHelp);
            },
            this
        );

        $(document).on('click', '.page-refresh', _.bind(function() {
                this.tempCache = this.getCachedData();
                this.clearPageCache(this.url);
                this.loadPage();
            }, this)
        );

        this.processForms(this.selectors.forms);
        this.processAnchors(this.selectorCached.container.find(this.selectors.scrollLinks));

        this.loadingMask = new Oro.LoadingMask();
        this.renderLoadingMask();
    },

    /**
     *  Triggered before hash navigation ajax request
     */
    beforeRequest: function() {
        this.gridRoute = ''; //clearing grid router
        this.loadingMask.show();
        Oro.pagestate.needServerRestore = true;
        /**
         * Backbone event. Fired before navigation ajax request is started
         * @event hash_navigation_request:start
         */
        Oro.Events.trigger("hash_navigation_request:start", this);
    },

    /**
     *  Triggered after hash navigation ajax request
     */
    afterRequest: function() {
        this.loadingMask.hide();
    },

    /**
     * Renders loading mask.
     *
     * @protected
     */
    renderLoadingMask: function() {
        this.selectorCached.loadingMask.append(this.loadingMask.render().$el);
        this.loadingMask.hide();
    },

    /**
     * Clearing content area with native js, prevents freezing of firefox with firebug enabled
     */
    clearContainer: function() {
        document.getElementById('container').innerHTML = '';
    },

    /**
     * Remove grid state params from url
     * @param url
     */
    removeGridParams: function(url) {
        return url.split('#g')[0];
    },

    /**
     * Make data more bulletproof.
     *
     * @param {String} rawData
     * @returns {Object}
     * @param prevPos
     */
    getCorrectedData: function(rawData, prevPos) {
        if (_.isUndefined(prevPos)) {
            prevPos = -1;
        }
        rawData = $.trim(rawData);
        var jsonStartPos = rawData.indexOf('{', prevPos + 1);
        var additionalData = '';
        var dataObj = null;
        if (jsonStartPos > 0) {
            additionalData = rawData.substr(0, jsonStartPos);
            var data = rawData.substr(jsonStartPos);
            try {
                dataObj = $.parseJSON(data);
            } catch (err) {
                return this.getCorrectedData(rawData, jsonStartPos);
            }
        } else if (jsonStartPos === 0) {
            dataObj = $.parseJSON(rawData);
        } else {
            throw "Unexpected content format";
        }

        if (additionalData) {
            additionalData = '<div class="alert alert-info fade in top-messages"><a class="close" data-dismiss="alert" href="#">&times;</a>'
                + '<div class="message">' + additionalData + '</div></div>';
        }

        if (dataObj.content !== undefined) {
            dataObj.content = additionalData + dataObj.content;
        }

        return dataObj;
    },

    /**
     * Handling ajax response data. Updating content area with new content, processing title and js
     *
     * @param {String} rawData
     * @param options
     */
    handleResponse: function (rawData, options) {
        if (_.isUndefined(options)) {
            options = {};
        }
        try {
            var data = (rawData.indexOf('http') === 0) ? {'redirect': true, 'fullRedirect': true, 'location': rawData} : this.getCorrectedData(rawData);
            if (_.isObject(data)) {
                if (data.redirect !== undefined && data.redirect) {
                    this.processRedirect(data);
                } else {
                    this.clearContainer();
                    var content = data.content;
                    /*if (options.fromCache) {
                        //don't load additional scripts for cached page to prevent dublicated scripts loading
                        content = content.replace(/<script.*?><\/script>/ig, '');
                    }*/
                    this.selectorCached.container.html(content);
                    this.selectorCached.menu.html(data.mainMenu);
                    /**
                     * Collecting javascript from head and append them to content
                     */
                    if (data.scripts.length) {
                        this.selectorCached.container.append(data.scripts);
                    }
                    /**
                     * Setting page title
                     */
                    document.title = data.title;
                    /**
                     * Setting serialized titles for pinbar and favourites buttons
                     */
                    var titleSerialized = data.titleSerialized;
                    if (titleSerialized) {
                        titleSerialized = $.parseJSON(titleSerialized);
                        $('.top-action-box .btn').filter('.minimize-button, .favorite-button').data('title', titleSerialized);
                    }
                    this.processClicks(this.selectorCached.menu.find(this.selectors.links));
                    this.processClicks(this.selectorCached.container.find(this.selectors.links));
                    this.processAnchors(this.selectorCached.container.find(this.selectors.scrollLinks));
                    this.processForms(this.selectorCached.container.find(this.selectors.forms));
                    this.processPinButton(data.showPinButton);
                    this.restoreFormState(this.tempCache);
                    if (!options.fromCache) {
                        this.updateMenuTabs(data);
                        this.addMessages(data.flashMessages);
                        Oro.Events.trigger("hash_navigation_request:refresh", this);
                    }
                    this.hideActiveDropdowns();
                }
            }
        }
        catch (err) {
            if (!_.isUndefined(console)) {
                console.error(err);
            }
            if (Oro.debug) {
                document.body.innerHTML = rawData;
            } else {
                this.showError('', Translator.get("Sorry, page was not loaded correctly"));
            }
        }
        this.triggerCompleteEvent();
    },

    processRedirect: function (data) {
        var redirectUrl = data.location;
        var urlParts = redirectUrl.split('url=');
        if (urlParts[1]) {
            redirectUrl = urlParts[1];
        }
        if(data.fullRedirect) {
            var delimiter = '?';
            if (redirectUrl.indexOf(delimiter) !== -1) {
                delimiter = '&';
            }
            window.location.replace(redirectUrl + delimiter + '_rand=' + Math.random());
        } else {
            this.clearPageCache(redirectUrl);
            this.setLocation(redirectUrl);
        }
    },

    /**
     * Show error message
     *
     * @param title
     * @param message
     */
    showError: function(title, message) {
        if (!_.isUndefined(Oro.BootstrapModal)) {
            var errorModal = new Oro.BootstrapModal({
                title: title,
                content: message,
                cancelText: false
            });
            errorModal.open();
        } else {
            alert(message);
        }
    },

    /**
     * Hide active dropdowns
     */
    hideActiveDropdowns: function() {
        this.selectorCached.searchDropdown.removeClass('header-search-focused');
        this.selectorCached.menuDropdowns.removeClass('open');
    },

    /**
     * Add session messages
     *
     * @param messages
     */
    addMessages: function(messages) {
        this.selectorCached['flashMessages'].find('.flash-messages-holder').empty();
        for (var type in messages) if (messages.hasOwnProperty(type)) {
            for (var i = 0; i < messages[type].length; i++) {
                Oro.NotificationFlashMessage(type, messages[type][i]);
            }
        }
    },

    /**
     * View / hide pins div
     *
     * @param showPinButton
     */
    processPinButton: function(showPinButton) {
        if (showPinButton) {
            this.selectorCached.pinButton.show();
        } else {
            this.selectorCached.pinButton.hide();
        }
    },

    /**
     * Update History and Most Viewed menu tabs
     *
     * @param data
     */
    updateMenuTabs: function(data) {
        this.selectorCached.historyTab.html(data.history);
        this.selectorCached.mostViewedTab.html(data.mostviewed);
        /**
         * Processing links for history and most viewed tabs
         */
        this.processClicks(this.selectorCached.historyTab.find(this.selectors.links));
        this.processClicks(this.selectorCached.mostViewedTab.find(this.selectors.links));
    },

    /**
     * Trigger hash navigation complete event
     */
    triggerCompleteEvent: function() {
        /**
         * Backbone event. Fired when hash navigation ajax request is complete
         * @event hash_navigation_request:complete
         */
        Oro.Events.trigger("hash_navigation_request:complete", this);
    },

    /**
     * Processing all links in selector and setting necessary click handler
     * links with "no-hash" class are not processed
     *
     * @param {String} selector
     */
    processClicks: function(selector) {
        $(selector).not('.no-hash').on('click', _.bind(function (e) {
            if (e.shiftKey || e.ctrlKey || e.metaKey || e.which === 2) {
                return true;
            }
            var target = e.currentTarget;
            e.preventDefault();
            var link = '';
            if ($(target).is('a')) {
                link = $(target).attr('href');
            } else if ($(target).is('span')) {
                link = $(target).attr('data-url');
            }
            if (link) {
                this.setLocation(link);
            }
            return false;
        }, this));
    },

    /**
     * Manually process anchors to prevent changing urls hash. If anchor doesn't have click events attached assume it
     * a standard anchor and emulate browser anchor scroll behaviour
     *
     * @param selector
     */
    processAnchors: function(selector) {
        $(selector).each(function() {
            var href = $(this).attr('href');
            var $href = /^#\w/.test(href) && $(href);
            if ($href) {
                var events = $._data($(this).get(0), 'events');
                if (_.isUndefined(events) || !events.click) {
                    $(this).on('click', function (e) {
                        e.preventDefault();
                        //finding parent div with scroll
                        var scrollDiv = $href.parents().filter(function() {
                            return $(this).get(0).scrollHeight > $(this).innerHeight();
                        });
                        if (!scrollDiv) {
                            scrollDiv = $(window);
                        } else {
                            scrollDiv = scrollDiv.eq(0);
                        }
                        scrollDiv.scrollTop($href.position().top + scrollDiv.scrollTop());
                        $(this).blur();
                    });
                }
            }
        });
    },

    /**
     * Processing forms submit events
     *
     * @param {String} selector
     */
    processForms: function(selector) {
        $(selector).on('submit', _.bind(function (e) {
            var target = e.currentTarget;
            e.preventDefault();

            var url = $(target).attr('action');
            this.method = $(target).attr('method') ? $(target).attr('method') : "get";

            if (url) {
                Oro.Registry.setElement('form_validate', true);
                Oro.Events.trigger("hash_navigation_request:form-start", target);
                if (Oro.Registry.getElement('form_validate')) {
                    var data = $(target).serialize();
                    if (this.method === 'get') {
                        if (data) {
                            url += '?' + data;
                        }
                        this.setLocation(url);
                    } else {
                        this.beforeRequest();
                        $(target).ajaxSubmit({
                            data:{'x-oro-hash-navigation' : true},
                            headers: { 'x-oro-hash-navigation': true },
                            error: _.bind(function (XMLHttpRequest, textStatus, errorThrown) {
                                this.showError('Error Message: ' + textStatus, 'HTTP Error: ' + errorThrown);
                                this.afterRequest();
                            }, this),
                            success: _.bind(function (data) {
                                data = this.getCorrectedData(data);
                                this.handleResponse(data);
                                this.afterRequest();
                            }, this)
                        });
                    }
                }
            }
            return false;
        }, this));
    },

    /**
     * Returns real url part from the hash
     * @param  {Boolean} includeGrid
     * @param  {Boolean} useRaw
     * @return {String}
     */
    getHashUrl: function(includeGrid, useRaw) {
        var url = this.url;
        if (!url || useRaw) {
            if (Backbone.history.fragment) {
                /**
                 * Get real url part from the hash without grid state
                 */
                var urlParts = Backbone.history.fragment.split('|g/');
                url = urlParts[0].replace('url=', '');
                if (urlParts[1] && (!_.isUndefined(includeGrid) && includeGrid === true)) {
                    url += '#g/' + urlParts[1];
                }
            }
            if (!url) {
                url = window.location.pathname + window.location.search;
            }
        }
        return url;
    },

    /**
     * Check if url is a 3d party link
     *
     * @param url
     * @return {Boolean}
     */
    checkThirdPartyLink: function(url) {
        var external = new RegExp('^(https?:)?//(?!' + location.host + ')');
        return (url.indexOf('http') !== -1) && external.test(url);
    },

    /**
     * Change location hash with new url
     *
     * @param {String} url
     * @param options
     */
    setLocation: function(url, options) {
        if (_.isUndefined(options)) {
            options = {};
        }
        if (this.enabled && !this.checkThirdPartyLink(url)) {
            if (options.clearCache) {
                this.clearPageCache();
            }
            this.useCache = false;
            if (options.useCache) {
                this.useCache = options.useCache;
            }
            url = url.replace(this.baseUrl, '').replace(/^(#\!?|\.)/, '');
            var pinbarView = Oro.Registry.getElement('pinbar_view');
            if (pinbarView) {
                var item = pinbarView.getItemForPage(url, true);
                if (item.length) {
                    url = item[0].get('url');
                }
            }
            url = url.replace('#g/', '|g/');
            if (url === this.getHashUrl() && !this.encodedStateData) {
                this.loadPage();
            } else {
                window.location.hash = '#url=' + url;
            }
        } else {
            window.location.href = url;
        }
    },

    /**
     * Processing back clicks
     *
     * @return {Boolean}
     */
    back: function() {
        window.history.back();
        return true;
    }
});

Oro.pageCacheStates = {
    state: {},

    registerStateObject: function(type, fields) {
        this.state[type] = {};
        _.each(fields, function(field) {
            this.state[type][field] = '';
        }, this)
    },

    saveObjectCache: function(type, values) {
        _.each(values, function(value, key) {
            this.state[type][key] = value;
        }, this)
    },

    getObjectCache: function(type) {
        return this.state[type];
    }
}

Oro.pageCacheStates.registerStateObject('grid',['collection']);
Oro.pageCacheStates.registerStateObject('form',['form_data']);

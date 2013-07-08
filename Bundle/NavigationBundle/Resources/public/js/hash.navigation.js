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
    enabled: false,

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

    skipAjaxCall: false,

    maxCachedPages: 2,

    contentCache: [],

    contentCacheUrls: [],

    /**
     * Routing default action
     *
     * @param {String} page
     * @param {String} encodedStateData
     */
    defaultAction: function (page, encodedStateData) {
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
    gridChangeStateAction: function (encodedStateData) {
        this.encodedStateData = encodedStateData;
    },

    /**
     *  Changing state for grid
     */
    gridChangeState: function () {
        if (this.gridRoute && this.encodedStateData && this.encodedStateData.length) {
            this.gridRoute.changeState(this.encodedStateData);
        }
    },

    /**
     * Initialize hash navigation
     *
     * @param options
     */
    initialize: function (options) {
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
    loadPage: function () {
        if (this.url) {
            this.beforeRequest();
            var i;
            if ((i = _.indexOf(this.contentCacheUrls, this.removePageStateParam(this.url))) !== -1) {
                if (this.contentCache[i]) {
                    this.handleResponse(this.contentCache[i], {fromCache: true});
                    this.clearPageCache(i);
                    //this.reorderCache(i);
                    //this.afterRequest();
                }
            }
            var pageUrl = this.baseUrl + this.url;
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
                    this.handleResponse(data);
                    this.updateDebugToolbar(jqXHR)
                    this.afterRequest();
                    this.savePageToCache(data);
                }, this)
            });
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
            $('.sf-toolbarreset').remove();
            $.get(
                this.baseUrl + entryPoint + '/_wdt/' + debugBarToken,
                _.bind(function(data) {
                    this.selectorCached['container'].append(data);
                }, this)
            );
        }
    },

    /**
     * Save page content to cache
     *
     * @param data
     */
    savePageToCache: function(data) {
        if (this.contentCacheUrls.length === this.maxCachedPages) {
            this.clearPageCache(0);
        }
        var j = _.indexOf(this.contentCacheUrls, this.removePageStateParam(this.url));
        if (j !== -1) {
            this.clearPageCache(j);
        }
        this.contentCacheUrls.push(this.removePageStateParam(this.url));
        this.contentCache[this.contentCacheUrls.length - 1] = data;
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
     * @param i
     */
    clearPageCache: function(i) {
        if (!_.isUndefined(i)) {
            this.contentCacheUrls.splice(i, 1);
            this.contentCache.splice(i, 1);
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
    init: function () {
        /**
         * Processing all links
         */
        this.processClicks(this.selectorCached.links);
        /**
         * Processing all links in grid after grid load
         */
        Oro.Events.bind(
            "grid_load:complete",
            function () {
                this.processClicks($('.grid-container').find(this.selectors.links));
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
        /**
         * Backbone event. Fired when hash navigation ajax request is complete
         * @event hash_navigation_request:complete
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
     * Make data more bulletproof.
     *
     * @param {String} data
     * @returns {Object}
     */
    getCorrectedData: function(data) {
        data = $.trim(data);
        var jsonStartPos = data.indexOf('{');
        var additionalData = '';
        if (jsonStartPos > 0) {
            additionalData = data.substr(0, jsonStartPos);
            data = data.substr(jsonStartPos);
        }
        var dataObj = (data.indexOf('http') === 0) ? {'redirect': true, 'fullRedirect': true, 'location': data} : $.parseJSON(data);

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
     * @param {String} data
     * @param options
     */
    handleResponse: function (data, options) {
        if (_.isUndefined(options)) {
            options = {};
        }
        try {
            data = this.getCorrectedData(data);
            if (data.redirect !== undefined && data.redirect) {
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
                    this.setLocation(redirectUrl);
                }
            } else {
                this.clearContainer();
                var content = data.content;
                if (options.fromCache) {
                    //don't load additional scripts for cached page to prevent dublicated scripts loading
                    content = content.replace(/<script.*?><\/script>/ig, '');
                }
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
                if (!options.fromCache) {
                    this.processClicks(this.selectorCached.menu.find(this.selectors.links));
                    this.processClicks(this.selectorCached.container.find(this.selectors.links));
                    this.processAnchors(this.selectorCached.container.find(this.selectors.scrollLinks));
                    this.updateMenuTabs(data);
                    this.processForms(this.selectorCached.container.find(this.selectors.forms));
                    this.addMessages(data.flashMessages);
                    this.processPinButton(data.showPinButton);
                    Oro.Events.trigger("hash_navigation_content:refresh", this);
                }
                this.hideActiveDropdowns();
            }
        }
        catch (err) {
            if (!_.isUndefined(console)) {
                console.error(err);
            }
            this.showError('', "Sorry, page was not loaded correctly");
        }
        if (!options.fromCache) {
            this.triggerCompleteEvent();
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
    updateMenuTabs: function (data) {
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
    triggerCompleteEvent: function () {
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
    processClicks: function (selector) {
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
    processForms: function (selector) {
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
                                this.handleResponse(data);
                                this.afterRequest();
                                //this.clearPageCache(); //clearing page cache after post request
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
     * @return {String}
     */
    getHashUrl: function (includeGrid) {
        var url = this.url;
        if (!url) {
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
    setLocation: function (url, options) {
        if (_.isUndefined(options)) {
            options = {};
        }
        if (this.enabled && !this.checkThirdPartyLink(url)) {
            if (options.clearCache) {
                this.clearPageCache();
            }
            url = url.replace(this.baseUrl, '').replace(/^(#\!?|\.)/, '').replace('#g/', '|g/');
            if (url === this.url) {
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
    back: function () {
        window.history.back();
        return true;
    }
});

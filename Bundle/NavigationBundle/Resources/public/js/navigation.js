/* jshint browser:true, devel:true */
/* global define */
define(['jquery', 'underscore', 'backbone', 'oro/translator', 'oro/app', 'oro/mediator', 'oro/messenger', 'oro/registry',
    'oro/modal', 'oro/loading-mask', 'oro/navigation/pagestate/view', 'oro/navigation/pagestate/model',
    'oro/pageable-collection', 'oro/widget-manager','jquery.form'],
function($, _, Backbone, __, app, mediator, messenger, registry,
         Modal, LoadingMask, PagestateView, PagestateModel,
         PageableCollection, widgetManager) {
    'use strict';

    var Navigation,
        instance,
        pageCacheStates = {
            state: {},

            registerStateObject: function(type, fields) {
                this.state[type] = {};
                _.each(fields, function(field) {
                    this.state[type][field] = '';
                }, this);
            },

            saveObjectCache: function(type, values) {
                _.each(values, function(value, key) {
                    this.state[type][key] = value;
                }, this);
            },

            getObjectCache: function(type) {
                return this.state[type];
            }
        };

    pageCacheStates.registerStateObject('grid',['collection']);
    pageCacheStates.registerStateObject('form',['form_data']);

    /**
     * Router for hash navigation
     *
     * @export  oro/navigation
     * @class   oro.Navigation
     * @extends Backbone.Router
     */
    Navigation = Backbone.Router.extend({
        /**
         * Hash navigation enabled/disabled flag
         */
        enabled: true,

        /**
         * links - Selector for all links that will be processed by hash navigation
         * scrollLinks - Selector for anchor links
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
         * breadcrumb - Selector for breadcrumb block
         * pinButton - Selector for pin, close and favorite buttons div
         *
         * @property
         */
        selectors: {
            links:               'a:not([href^=#],[href^=javascript],[href^=mailto],[href^=skype],[href^=ftp],[href^=callto],[href^=tel]),span[data-url]',
            scrollLinks:         'a[href^=#]',
            content:             '#content',
            userMenu:            '#top-page .user-menu',
            container:           '#container',
            loadingMask:         '.hash-loading-mask',
            searchDropdown:      '#search-div',
            menuDropdowns:       '.pin-menus.dropdown, .nav .dropdown',
            pinbarHelp:          '.pin-bar-empty',
            historyTab:          '#history-content',
            mostViewedTab:       '#mostviewed-content',
            flashMessages:       '#flash-messages',
            menu:                '#main-menu',
            breadcrumb:          '#breadcrumb',
            pinButtonsContainer: '#pin-button-div',
            gridContainer:       '.grid-container',
            pinButtons:          '.minimize-button, .favorite-button'
        },
        selectorCached: {},

        /** @property {oro.LoadingMask} */
        loadingMask: '',

        /** @property {String} */
        baseUrl: '',

        /** @property {String} */
        headerId: '',

        /** @property {Object} */
        headerObject: '',

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

        /** @property {oro.datagrid.Router} */
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

        skipGridStateChange: false,

        maxCachedPages: 10,

        contentCache: [],

        contentCacheUrls: [],

        tempCache: '',

        formState: '',

        cacheTimer: null,

        confirmModal: null,

        notificationMessage: null,

        outdatedMessage: '',

        /**
         * Routing default action
         *
         * @param {String} page
         * @param {String} encodedStateData
         */
        defaultAction: function(page, encodedStateData) {
            this.beforeDefaultAction();
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

        beforeDefaultAction: function() {
            //reset pagestate restore flag in case we left the page
            if (this.url !== this.getHashUrl(false, true)) {
                this.getPagestate().needServerRestore = true;
            }
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

            this.baseUrl =  options.baseUrl;
            this.headerId = options.headerId;
            var header = {};
            header[this.headerId] = true;
            this.headerObject = header;
            if (window.location.hash === '') {
                //skip ajax page refresh for the current page
                this.skipAjaxCall = true;
            }

            this.init();

            Backbone.Router.prototype.initialize.apply(this, arguments);
        },

        getPagestate: function() {
            if (!this.pagestate) {
                this.pagestate = new PagestateView({
                    model: new PagestateModel()
                });
            }
            return this.pagestate;
        },

        /**
         * Ajax call for loading page content
         */
        loadPage: function() {
            if (this.url) {
                this.beforeRequest();
                var cacheData;
                if (cacheData = this.getCachedData()) {
                    widgetManager.resetWidgets();
                    this.tempCache = cacheData;
                    this.handleResponse(cacheData, {fromCache: true});
                    this.validatePageCache(cacheData);
                    this.afterRequest();
                } else {
                    var pageUrl = this.baseUrl + this.url;
                    var stringState = [];
                    this.skipGridStateChange = false;
                    if (this.encodedStateData) {
                        var state = PageableCollection.prototype.decodeStateData(this.encodedStateData);
                        var collection = new PageableCollection({}, {inputName: state.gridName});

                        stringState = collection.processQueryParams({}, state);
                        stringState = collection.processFiltersParams(stringState, state);

                        mediator.once(
                            "datagrid_filters:rendered",
                            function (collection) {
                                collection.trigger('updateState', collection);
                            },
                            this
                        );

                        this.skipGridStateChange = true;
                    }

                    var useCache = this.useCache;
                    $.ajax({
                        url: pageUrl,
                        headers: this.headerObject,
                        data: stringState,
                        beforeSend: function( xhr ) {
                            $.isActive(false);
                            //remove standard ajax header because we already have a custom header sent
                            xhr.setRequestHeader('X-Requested-With', {toString: function(){ return ''; }});
                        },

                        error: _.bind(this.processError, this),

                        success: _.bind(function (data, textStatus, jqXHR) {
                            if (!cacheData) {
                                this.handleResponse(data);
                                this.updateDebugToolbar(jqXHR);
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

        /**
         * Restore form state from cache
         *
         * @param cacheData
         */
        restoreFormState: function(cacheData) {
            var formState = {},
                pagestate = this.getPagestate();
            if (this.formState) {
                formState = this.formState;
            } else if (cacheData.states) {
                formState = cacheData.states.getObjectCache('form');
            }
            if (formState['form_data'] && formState['form_data'].length) {
                pagestate.updateState(formState['form_data']);
                pagestate.restore();
                pagestate.needServerRestore = false;
            }
        },

        initCacheTimer: function() {
            this.clearCacheTimer();
            this.cacheTimer = setInterval(_.bind(function() {
                var cacheData = this.getCachedData();
                if (cacheData) {
                    if (!cacheData.is_entity_page) {
                        //validating grid states only for non-entity pages
                        var hasGridCache = this.validateGridStates(cacheData);
                        //validating content md5 only if no cached grids found on page
                        if (!hasGridCache) {
                            this.validateMd5Request(cacheData);
                        }
                    } else {
                        this.validateMd5Request(cacheData);
                    }
                }
            }, this), 5000);
        },

        clearCacheTimer: function() {
            clearInterval(this.cacheTimer);
        },

        /**
         * Validate page cache comparing cached content md5 with the one from server
         *
         * @param cacheData
         */
        validateMd5Request: function(cacheData) {
            var pageUrl = this.baseUrl + this.url;
            var url = this.url;
            var params = {};
            params[this.headerId] = true;
            params['hash-navigation-md5'] = true;
            $.ajax({
                url: pageUrl,
                data: params,
                error: _.bind(function (jqXHR, textStatus, errorThrown) {
                }, this),

                success: _.bind(function (data, textStatus, jqXHR) {
                    if (this.getCorrectedData(data).content_md5 !== cacheData.content_md5) {
                        this.showOutdatedMessage(url);
                    }
                }, this)
            });
        },

        /**
         * Validate grid state based on grid collection
         *
         * @param cacheData
         * @return true if grid cache is found and false otherwise
         */
        validateGridStates: function(cacheData) {
            if (cacheData.states) {
                var formState = cacheData.states.getObjectCache('form');
                var girdState = cacheData.states.getObjectCache('grid');
                //grid states on form pages are not validated
                if (girdState['collection'] && !formState['form_data']) {
                    var collection = girdState['collection'].clone();
                    var cachedCollection = girdState['collection'];
                    var url = this.url;
                    var options = {ignoreSaveStateInUrl: true};
                    /**
                     * Comparing cached collection with fetched from server
                     */
                    options.success = _.bind(function () {
                        if (!_.isEqual(cachedCollection.toJSON(),collection.toJSON())) {
                            this.showOutdatedMessage(url);
                        }
                    }, this);
                    options.error = _.bind(this.showOutdatedMessage, this, url);
                    collection.fetch(options);
                    return true;
                }
            }

            return false;
        },

        /**
         * Validate page cache to check if its up to date. Comparing grid state(if any) and content md5
         *
         * @param cacheData
         */
        validatePageCache: function(cacheData) {
            this.validateGridStates(cacheData);
            this.validateMd5Request(cacheData);
        },

        /**
         * Show "refresh page" message
         *
         * @param url
         */
        showOutdatedMessage: function(url) {
            this.clearCacheTimer();
            if (this.useCache && this.url === url) {
                if (!this.notificationMessage) {
                    var message = __("Content of the page is outdated, please %click here% to refresh the page");
                    this.outdatedMessage = message.replace(/%(.*)%/,"<span class='page-refresh'>$1</span>");
                } else {
                    this.notificationMessage.close();
                }
                this.notificationMessage = messenger.notificationMessage('warning', this.outdatedMessage);
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
                        if (!container.closest('body').length) {
                            container = $(document.body);
                        }
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
            this.tempCache.states = app.deepClone(pageCacheStates);
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
         */
        updateCachedContent: function(objectName, state) {
            if (this.tempCache.states) {
                this.tempCache.states.saveObjectCache(objectName, state);
            }
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
             * Processing all links in grid after grid load
             */
            mediator.bind(
                "grid_load:complete",
                function (collection) {
                    this.updateCachedContent('grid', {'collection': collection});
                    var pinbarView = registry.getElement('pinbar_view');
                    if (pinbarView) {
                        var item = pinbarView.getItemForCurrentPage(true);
                        if (item.length && this.useCache) {
                            this.addCurrentPageToCache();
                        }
                    }
                    this.processGridLinks();
                },
                this
            );

            /**
             * Loading grid collection from cache
             */
            mediator.bind(
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
                    } else { //updating temp cache with collection
                        this.updateCachedContent('grid', {'collection': datagridCollection.collection});
                    }
                },
                this
            );

            /**
             * Trigger updateState event for grid collection if page was loaded from cache
             */
            mediator.bind(
                "datagrid_filters:rendered",
                function (collection) {
                    if (this.getCachedData() && this.encodedStateData) {
                        collection.trigger('updateState', collection);
                    }
                },
                this
            );

            /**
             * Clear page cache for unpinned page
             */
            mediator.bind(
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
            mediator.bind(
                "pinbar_item_minimized",
                function () {
                    this.useCache = true;
                    this.addCurrentPageToCache();
                },
                this
            );

            /**
             * Add "pinned" page to cache
             */
            mediator.bind(
                "pagestate_collected",
                function (pagestateModel) {
                    this.updateCachedContent('form', {'form_data': pagestateModel.get('pagestate').data});
                    if (this.useCache) {
                        this.addCurrentPageToCache();
                    }
                },
                this
            );

            /**
             * Processing navigate action execute
             */
            mediator.bind(
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
            mediator.bind(
                "grid_route:loaded",
                function (route) {
                    this.gridRoute = route;
                    if (!this.skipGridStateChange) {
                        this.gridChangeState();
                    }
                    this.processGridLinks();
                },
                this
            );

            /**
             * Processing links in 3 dots menu after item is added (e.g. favourites)
             */
            mediator.bind(
                "navigation_item:added",
                function (item) {
                    this.processClicks(item.find(this.selectors.links));
                },
                this
            );

            /**
             * Processing links in search result dropdown
             */
            mediator.bind(
                "top_search_request:complete",
                function () {
                    this.processClicks($(this.selectorCached.searchDropdown).find(this.selectors.links));
                },
                this
            );

            /**
             * Processing pinbar help link
             */
            mediator.bind(
                "pinbar_help:shown",
                function () {
                    this.processClicks(this.selectors.pinbarHelp);
                },
                this
            );

            this.confirmModal = new Modal({
                title: __('Refresh Confirmation'),
                content: __('Your local changes will be lost. Are you sure you want to refresh the page?'),
                okText: __('Ok, got it.'),
                className: 'modal modal-primary',
                okButtonClass: 'btn-primary btn-large',
                cancelText: __('Cancel')
            });
            this.confirmModal.on('ok', _.bind(function() {
                this.refreshPage();
            }, this));

            $(document).on('click', '.page-refresh', _.bind(function() {
                    var data = this.getCachedData();
                    var formState;
                    if (data.states) {
                        formState = data.states.getObjectCache('form');
                        /**
                         *  saving form state for future restore after content refresh, uncomment after new page states logic is
                         *  implemented
                         */
                        //this.formState = formState;
                    }
                if (formState && formState['form_data'].length) {
                        this.confirmModal.open();
                    } else {
                        this.refreshPage();
                    }
                }, this)
            );

            /**
             * Processing all links
             */
            this.processClicks(this.selectorCached.links);
            this.disableEmptyLinks(this.selectorCached.menu.find(this.selectors.scrollLinks));

            this.processForms();
            this.processAnchors(this.selectorCached.container.find(this.selectors.scrollLinks));

            this.loadingMask = new LoadingMask();
            this.renderLoadingMask();
        },

        /**
         *  Triggered before hash navigation ajax request
         */
        beforeRequest: function() {
            this.loadingMask.show();
            this.gridRoute = ''; //clearing grid router
            this.tempCache = '';
            clearInterval(this.cacheTimer);
            if (this.notificationMessage) {
                this.notificationMessage.close();
            }
            /**
             * Backbone event. Fired before navigation ajax request is started
             * @event hash_navigation_request:start
             */
            mediator.trigger("hash_navigation_request:start", this);
        },

        /**
         *  Triggered after hash navigation ajax request
         */
        afterRequest: function() {
            this.formState = '';
            this.initCacheTimer();
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

        refreshPage: function() {
            this.clearPageCache(this.url);
            this.loadPage();
        },

        /**
         * Clearing content area with native js, prevents freezing of firefox with firebug enabled.
         * If no container found, reload the page
         */
        clearContainer: function() {
            var container = document.getElementById('container');
            if (container) {
                container.innerHTML = '';
            } else {
                location.reload();
            }
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
                var data = rawData;
                if (!options.fromCache) {
                    data = (rawData.indexOf('http') === 0) ? {'redirect': true, 'fullRedirect': true, 'location': rawData} : this.getCorrectedData(rawData);
                }
                if (_.isObject(data)) {
                    if (data.redirect !== undefined && data.redirect) {
                        this.processRedirect(data);
                    } else {
                        if (!options.fromCache && !options.skipCache) {
                            this.savePageToCache(data);
                        }
                        this.clearContainer();
                        var content = data.content;
                        this.selectorCached.container.html(content);
                        this.selectorCached.menu.html(data.mainMenu);
                        this.selectorCached.userMenu.html(data.userMenu);
                        this.selectorCached.breadcrumb.html(data.breadcrumb);
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
                        this.processClicks(this.selectorCached.menu.find(this.selectors.links));
                        this.processClicks(this.selectorCached.userMenu.find(this.selectors.links));
                        this.disableEmptyLinks(this.selectorCached.menu.find(this.selectors.scrollLinks));
                        this.processClicks(this.selectorCached.container.find(this.selectors.links));
                        this.processAnchors(this.selectorCached.container.find(this.selectors.scrollLinks));
                        this.processPinButton(data);
                        this.restoreFormState(this.tempCache);
                        if (!options.fromCache) {
                            this.updateMenuTabs(data);
                            this.addMessages(data.flashMessages);
                        }
                        this.hideActiveDropdowns();
                        mediator.trigger("hash_navigation_request:refresh", this);
                        this.loadingMask.hide();
                    }
                }
            }
            catch (err) {
                if (!_.isUndefined(console)) {
                    console.error(err);
                }
                if (app.debug) {
                    document.body.innerHTML = rawData;
                } else {
                    this.showMessage(__('Sorry, page was not loaded correctly'));
                }
            }
            this.triggerCompleteEvent();
        },

        /**
         * Disable # links to prevent hash changing
         *
         * @param selector
         */
        disableEmptyLinks: function(selector) {
            $(selector).on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
        },

        processGridLinks: function()
        {
            this.processClicks($(this.selectors.gridContainer).find(this.selectors.links));
        },

        processRedirect: function (data) {
            var redirectUrl = data.location;
            var urlParts = redirectUrl.split('url=');
            if (urlParts[1]) {
                redirectUrl = urlParts[1];
            }
            $.isActive(true);
            if(data.fullRedirect) {
                var delimiter = '?';
                if (redirectUrl.indexOf(delimiter) !== -1) {
                    delimiter = '&';
                }
                window.location.replace(redirectUrl + delimiter + '_rand=' + Math.random());
            } else {
                //clearing cache for current and redirect urls, e.g. form and grid page
                this.clearPageCache(this.url);
                this.clearPageCache(redirectUrl);
                this.setLocation(redirectUrl);
            }
        },

        /**
         * Show error message
         *
         * @param {XMLHttpRequest} XMLHttpRequest
         * @param {String} textStatus
         * @param {String} errorThrown
         */
        processError: function(XMLHttpRequest, textStatus, errorThrown) {
            var message403 = 'You do not have permission to this action';
            if (app.debug) {
                if (XMLHttpRequest.status == 403) {
                    this.showMessage(__(message403));
                    this.loadingMask.hide();
                } else {
                    document.body.innerHTML = XMLHttpRequest.responseText;
                }
                this.updateDebugToolbar(XMLHttpRequest);
            } else {
                var message = 'Sorry, page was not loaded correctly';
                if (XMLHttpRequest.status == 403) {
                    message = message403;
                }
                this.showMessage(__(message));
                this.loadingMask.hide();
            }
        },

        showMessage: function(message) {
            messenger.notificationFlashMessage('error', message);
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
            this.selectorCached.flashMessages.find('.flash-messages-holder').empty();
            for (var type in messages) {
                if (messages.hasOwnProperty(type)) {
                    for (var i = 0; i < messages[type].length; i++) {
                        messenger.notificationFlashMessage(type, messages[type][i]);
                    }
                }
            }
        },

        /**
         * View / hide pins div and set titles
         *
         * @param showPinButton
         */
        processPinButton: function(data) {
            if (data.showPinButton) {
                this.selectorCached.pinButtonsContainer.show();
                /**
                 * Setting serialized titles for pinbar and favourites buttons
                 */
                var titleSerialized = data.titleSerialized;
                if (titleSerialized) {
                    titleSerialized = $.parseJSON(titleSerialized);
                    this.selectorCached.pinButtonsContainer.find(this.selectors.pinButtons).data('title', titleSerialized);
                }
                this.selectorCached.pinButtonsContainer.find(this.selectors.pinButtons).data('title-rendered-short', data.titleShort);
            } else {
                this.selectorCached.pinButtonsContainer.hide();
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
            mediator.trigger("hash_navigation_request:complete", this);
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
                    var event = {stoppedProcess: false, hashNavigationInstance: this, link: link};
                    mediator.trigger("hash_navigation_click", event);
                    if (event.stoppedProcess === false) {
                        this.setLocation(link);
                    }
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
         */
        processForms: function() {
            $('body').on('submit', _.bind(function (e) {
                var $form = $(e.target);
                if ($form.data('nohash')) {
                    return;
                }
                e.preventDefault();
                if ($form.data('sent')) {
                    return;
                }

                var url = $form.attr('action');
                this.method = $form.attr('method') || "get";

                if (url) {
                    $form.data('sent', true);
                    registry.setElement('form_validate', true);
                    mediator.trigger("hash_navigation_request:form-start", $form.get(0));
                    if (registry.getElement('form_validate')) {
                        var data = $form.serialize();
                        if (this.method === 'get') {
                            if (data) {
                                url += '?' + data;
                            }
                            this.setLocation(url);
                            $form.removeData('sent');
                        } else {
                            this.beforeRequest();
                            $form.ajaxSubmit({
                                data: this.headerObject,
                                headers: this.headerObject,
                                complete: function(){
                                    $form.removeData('sent');
                                },
                                error: _.bind(this.processError, this),
                                success: _.bind(function (data) {
                                    this.handleResponse(data, {'skipCache' : true}); //don't cache form submit response
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
                var pinbarView = registry.getElement('pinbar_view');
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
         * @return {Boolean}
         */
        checkHashForUrl: function() {
            return window.location.hash.indexOf('#url=') !== -1;
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

    /**
     * Fetches flag - hash navigation is enabled or not
     *
     * @returns {boolean}
     */
    Navigation.isEnabled = function() {
        return Boolean(Navigation.prototype.enabled);
    };

    /**
     * Fetches navigation (Oro router) instance
     *
     * @returns {oro.Navigation}
     */
    Navigation.getInstance = function() {
        return instance;
    };

    /**
     * Creates navigation instance
     *
     * @param {Object} options
     */
    Navigation.setup = function(options) {
        instance = new Navigation(options);
    };

    return Navigation;
});

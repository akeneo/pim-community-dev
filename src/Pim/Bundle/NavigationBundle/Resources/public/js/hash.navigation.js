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
     * forms - Selector for all forms that will be processed by hash navigation
     * content - Selector for ajax response content area
     * container - Selector for main content area
     * loadingMask - Selector for loading spinner
     * searchDropdown - Selector for dropdown with search results
     * menuDropdowns - Selector for 3 dots menu and my profile dropdowns
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
        "url=*page(|g/*encodedStateData)": "defaultAction",
        "g/*encodedStateData": "gridChangeStateAction"
    },

    /**
     * Routing default action
     *
     * @param {String} page
     * @param {String} encodedStateData
     */
    defaultAction: function (page, encodedStateData) {
        this.encodedStateData = encodedStateData;
        this.url = page;
        this.loadPage(this.url);
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
        if (this.gridRoute) {
            this.gridRoute.changeState(this.encodedStateData);
        }
    },

    /**
     * Initialaize hash navigation
     *
     * @param options
     */
    initialize: function (options) {
        options = options || {};
        if (!options.baseUrl) {
            throw new TypeError("'baseUrl' is required");
        }

        this.baseUrl = options.baseUrl;

        this.init();

        Backbone.Router.prototype.initialize.apply(this, arguments);
    },

    /**
     * Ajax call for loading page content
     */
    loadPage: function () {
        if (this.url) {
            this.beforeRequest();
            var pageUrl = this.baseUrl + this.url;
            $.ajax({
                url: pageUrl,
                headers: { 'x-oro-hash-navigation': true },
                beforeSend: function( xhr ) {
                    //remove standard ajax header because we already have a custom header sent
                    xhr.setRequestHeader('X-Requested-With', {toString: function(){ return ''; }});
                },
                error: _.bind(function (XMLHttpRequest, textStatus, errorThrown) {
                    alert('Error Message: ' + textStatus);
                    alert('HTTP Error: ' + errorThrown);
                    this.afterRequest();
                }, this),

                success: _.bind(function (data) {
                    this.handleResponse(data);
                    this.afterRequest();
                }, this)
            });
        }
    },

    /**
     * Init
     */
    init: function () {
        /**
         * Processing all links
         */
        this.processClicks(this.selectors.links);
        /**
         * Processing all links in grid after grid load
         */
        Oro.Events.bind(
            "grid_load:complete",
            function () {
                this.processClicks('.grid-container ' + this.selectors.links)
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
                this.processClicks(this.selectors.searchDropdown + ' ' + this.selectors.links);
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
        $(this.selectors.loadingMask).append(this.loadingMask.render().$el);
        this.loadingMask.hide();
    },

    /**
     * Handling ajax response data. Updating content area with new content, processing title and js
     *
     * @param {String} data
     */
    handleResponse: function (data) {
        try {
            /**
             * Clearing content area with native js, prevents freezing of firefox with firebug enabled
             */
            document.getElementById('container').innerHTML = '';
            var redirectUrl = $(data).filter('#redirect').text();
            if (redirectUrl) {
                urlParts = redirectUrl.split('url=');
                if (urlParts[1]) {
                    redirectUrl = urlParts[1];
                }
                if($(data).filter('#redirect').attr('data-redirect')) {
                    window.location.replace(redirectUrl);
                } else {
                    this.setLocation(redirectUrl);
                }
            } else {
                $(this.selectors.container).html($(data).filter(this.selectors.content).html());
                $(this.selectors.menu).html($(data).filter(this.selectors.menu).html());
                /**
                 * Collecting javascript from head and append them to content
                 */
                var js = '';
                $(data).filter('#head').find('script:not([src])').each(function () {
                    js = js + this.outerHTML;
                })
                $(this.selectors.container).append(js);
                /**
                 * Setting page title
                 */
                document.title = $(data).filter('#head').find('#title').html();
                /**
                 * Setting serialized titles for pinbar and favourites buttons
                 */
                var titleSerialized = $(data).filter('#head').find('#title-serialized').html();
                if (titleSerialized) {
                    titleSerialized = $.parseJSON(titleSerialized);
                    $('.top-action-box .btn').filter('.minimize-button, .favorite-button').data('title', titleSerialized);
                }

                this.processClicks(this.selectors.menu + ' ' + this.selectors.links);
                this.processClicks(this.selectors.container + ' ' + this.selectors.links);
                this.updateMenuTabs(data);
                this.processForms(this.selectors.container + ' ' + this.selectors.forms);
                this.updateMessages(data);
                this.hideActiveDropdowns();
                this.processPinButton(data);
            }
        }
        catch (err) {
            console.log(err);
            alert("Sorry, unable to load page");
        }
        this.triggerCompleteEvent();
    },

    /**
     * Hide active dropdowns
     */
    hideActiveDropdowns: function() {
        $(this.selectors.searchDropdown).removeClass('header-search-focused');
        $(this.selectors.menuDropdowns).removeClass('open');
    },

    /**
     * Updating session messages block
     *
     * @param data
     */
    updateMessages: function(data) {
        $(this.selectors.flashMessages).html($(data).filter(this.selectors.flashMessages).html());
    },

    /**
     * View / hide pins div
     *
     * @param data
     */
    processPinButton: function(data) {
        if ($(data).filter(this.selectors.pinButton).html().indexOf("true") != - 1) {
            $(this.selectors.pinButton).show();
        } else {
            $(this.selectors.pinButton).hide();
        }
    },

    /**
    * Update History and Most Viewed menu tabs
    *
    * @param data
    */
    updateMenuTabs: function (data) {
        $(this.selectors.historyTab).html($(data).filter(this.selectors.historyTab).html());
        $(this.selectors.mostViewedTab).html($(data).filter(this.selectors.mostViewedTab).html());
        /**
          * Processing links for history and most viewed tabs
          */
        this.processClicks(this.selectors.historyTab + ' ' + this.selectors.links + ', ' +
                this.selectors.mostViewedTab + ' ' + this.selectors.links);
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
            if (e.shiftKey || e.ctrlKey || e.metaKey || e.which == 2) {
                return true;
            }
            var target = e.currentTarget;
            e.preventDefault();
            var link = '';
            if ($(target).is('a')) {
                link = $(target).attr('href');
                if ($(target).hasClass('back')) {
                    //if back link is found, go back and don't do further processing
                    if (this.back()) {
                        return false;
                    }
                }
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
     * Processing forms submit events
     *
     * @param {String} selector
     */
    processForms: function (selector) {
        $(selector).on('submit', _.bind(function (e) {
            var target = e.currentTarget;
            e.preventDefault();

            var url = '';
            url = $(target).attr('action');
            this.method = $(target).attr('method') ? $(target).attr('method') : "get";

            if (url) {
                var data = $(target).serialize();
                if (this.method == 'get') {
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
                            alert('Error Message: ' + textStatus + ' HTTP Error: ' + errorThrown);
                            this.afterRequest();
                        }, this),
                        success: _.bind(function (data) {
                            this.handleResponse(data);
                            this.afterRequest();
                        }, this)
                    });
                }
            }
            return false;
        }, this))
    },

    /**
     * Returns real url part from the hash
     * @return {String}
     */
    getHashUrl: function (includeGrid) {
        var url = this.url;
        if (!url) {
            /**
             * Get real url part from the hash without grid state
             */
            var urlParts = Backbone.history.fragment.split('|g/');
            url = urlParts[0].replace('url=', '');
            if (urlParts[1] && (!_.isUndefined(includeGrid) && includeGrid == true)) {
                url += '#g/' + urlParts[1];
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
     * @todo Implement check
     * @param url
     * @return {Boolean}
     */
    checkThirdPartyLink: function(url) {
        return false;
    },

    /**
     * Change location hash with new url
     *
     * @param {String} url
     */
    setLocation: function (url) {
        if (this.enabled && !this.checkThirdPartyLink(url)) {
            url = url.replace(this.baseUrl, '').replace(/^(#\!?|\.)/, '').replace('#g/', '|g/');
            window.location.hash = '#url=' + url;
        } else {
            window.location = url;
        }
    },

    /**
     * Processing back clicks. If we have back attribute in url, use it, otherwise using browser back
     *
     * @return {Boolean}
     */
    back: function () {
        var backFound = false;
        var url = new Url(this.getHashUrl());
        if (url.query.back) {
            var backUrl = new Url(url.query.back);
            if (backUrl.hash.indexOf('url=') !== -1) {
                window.location = url.query.back;
            } else {
                this.setLocation(backUrl.path);
            }
            backFound = true;
        } else {
            window.history.back();
            backFound = true;
        }
        return backFound;
    }
});

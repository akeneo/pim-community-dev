/*jslint browser: true, vars: true, nomen: true*/
/*jshint browser: true, devel: true*/
/*global define*/
define(function (require) {
    'use strict';

    var $ = require('jquery');
    var _ = require('underscore');
    var Backbone = require('backbone');
    var __ = require('oro/translator');
    var app = require('oro/app');
    var mediator = require('oro/mediator');
    var messenger = require('oro/messenger');
    var LoadingMask = require('oro/loading-mask');
    require('jquery.form');

    var Navigation;
    var instance;
    var pinbarView = null;
    var flashMessages = [];

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
            userMenu:            '#top-page .user-menu',
            container:           '#container',
            loadingMask:         '.hash-loading-mask',
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
         * Url part
         *
         * @property
         */
        url: '',

        /** @property */
        routes: {
            "(url=*page)(|g/*encodedStateData)": "defaultAction"
        },

        skipAjaxCall: false,

        notificationMessage: null,

        /**
         * Routing default action
         *
         * @param {String} page
         * @param {String} encodedStateData
         */
        defaultAction: function(page, encodedStateData) {
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

        /**
         * Ajax call for loading page content
         */
        loadPage: function() {
            if (this.url) {
                this.beforeRequest();

                var pageUrl = this.baseUrl + this.url;
                var stringState = [];

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
                        this.handleResponse(data);
                        this.updateDebugToolbar(jqXHR);
                        this.afterRequest();
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
         * Init
         */
        init: function() {
            /**
             * Processing all links in grid after grid load
             */
            mediator.bind(
                "grid_load:complete grid_route:loaded",
                function () {
                    this.processGridLinks();
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
             * Processing pinbar help link
             */
            mediator.bind(
                "pinbar_help:shown",
                function () {
                    this.processClicks(this.selectors.pinbarHelp);
                },
                this
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
            var message;
            while (message = flashMessages.shift()) {
                messenger.notificationFlashMessage.apply(messenger, message);
            }
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
                additionalData = '<div class="alert alert-info fade in top-messages">' +
                '<a class="close" data-dismiss="alert" href="#">&times;</a>' +
                '<div class="message">' + additionalData + '</div></div>';
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
                data = (rawData.indexOf('http') === 0) ? {'redirect': true, 'fullRedirect': true, 'location': rawData} : this.getCorrectedData(rawData);
                if (_.isObject(data)) {
                    if (data.redirect !== undefined && data.redirect) {
                        this.processRedirect(data);
                    } else {
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
                        this.updateMenuTabs(data);
                        this.addMessages(data.flashMessages);
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

        processGridLinks: function() {
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
         * Adds a flash message to be displayed on next page load
         * @see oro/messenger
         */
        addFlashMessage: function() {
            flashMessages.push(arguments);
        },

        /**
         * View / hide pins div and set titles
         *
         * @param showPinButton
         */
        processPinButton: function(data) {
            if (data.showPinButton) {
                this.selectorCached.pinButtonsContainer.removeClass('AknButtonList--hide');
                /**
                 * Setting serialized titles for pinbar and favourites buttons
                 */
                var titleSerialized = data.titleSerialized;
                if (titleSerialized) {
                    titleSerialized = $.parseJSON(titleSerialized);
                    this.setPinButtonsData('title', titleSerialized);
                }
                this.setPinButtonsData('title-rendered-short', data.titleShort);
            } else {
                this.selectorCached.pinButtonsContainer.addClass('AknButtonList--hide');
            }
        },

        /**
         * Get data linked to pin buttons
         *
         * @param {string} key
         *
         * @returns {*}
         */
        getPinButtonsData: function (key) {
            var buttons = this.selectorCached.pinButtonsContainer.find(this.selectors.pinButtons);
            if (buttons.length) {
                return buttons.data(key);
            }

            return null;
        },

        /**
         * Set data linked to pin buttons
         *
         * @param {string} key
         * @param {*}      value
         */
        setPinButtonsData: function (key, value) {
            var buttons = this.selectorCached.pinButtonsContainer.find(this.selectors.pinButtons);
            if (buttons.length) {
                buttons.data(key, value);
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
                if ($form.data('nohash') || e.isDefaultPrevented()) {
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
                    var formStartSettings = {
                        form_validate: true
                    };
                    mediator.trigger('hash_navigation_request:form-start', $form.get(0), formStartSettings);
                    if (formStartSettings.form_validate) {
                        var data = $form.serialize();
                        var submit = $(document.activeElement,$form);
                        data += '&'+submit.attr('name')+'=1';
                        if (this.method === 'get') {
                            if (data) {
                                url += '?' + data;
                            }
                            this.setLocation(url);
                            $form.removeData('sent');
                        } else {
                            this.beforeRequest();
                            var additionalData = this.headerObject;
                            additionalData[submit.attr('name')] = true;
                            $form.ajaxSubmit({
                                data: additionalData,
                                headers: this.headerObject,
                                complete: function(){
                                    $form.removeData('sent');
                                },
                                error: _.bind(this.processError, this),
                                success: _.bind(function (data) {
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
                url = url.replace(this.baseUrl, '').replace(/^(#\!?|\.)/, '');
                if (pinbarView) {
                    var item = pinbarView.getItemForPage(url, true);
                    if (item.length) {
                        url = item[0].get('url');
                    }
                }
                url = url.replace('#g/', '|g/');
                if (url === this.getHashUrl()) {
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

    /**
     * Register Pinbar view instance
     *
     * @param {Object} pinbarView
     */
    Navigation.registerPinbarView = function (instance) {
        pinbarView = instance;
    };

    return Navigation;
});

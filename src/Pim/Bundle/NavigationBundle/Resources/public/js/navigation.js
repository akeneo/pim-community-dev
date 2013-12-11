/**
 * Extends Oro Navigation to automatically navigate to last filter status
 *
 * @param {type} OroNavigation
 * @returns {unresolved}
 */
define(
    ['oro/navigation', 'oro/app', 'oro/messenger', 'underscore'],
    function(OroNavigation, app, messenger, _) {

        var QUERY_STRING_REGEX = /^[^\?]+\??/,
            URL_PATH_REGEX = /\?.+/,
            flashMessages = [],
            parent = OroNavigation.prototype,
            instance,
            Navigation = OroNavigation.extend({
                /**
                 * @inheritdoc
                 */
                initialize: function (options) {
                    this.gridRegexps = options.gridRegexps;
                    parent.initialize.call(this, options);
                },
                /**
                 * @inheritdoc
                 */
                defaultAction: function(page, encodedStateData) {
                    this.beforeDefaultAction();
                    this.encodedStateData = encodedStateData;
                    this.url = page;
                    if (!this.url) {
                        this.url = window.location.href.replace(this.baseUrl, '');
                    }
                    var gridName = (function(url, gridRegexps) {
                        return _.reduce(gridRegexps, function(memo, regexp, gridName) {
                            return regexp.test(url) ? gridName : memo;
                        }, null);
                    })(this.url.replace(URL_PATH_REGEX, ''), this.gridRegexps);
                    if (gridName) {
                        var qs = this.url.replace(QUERY_STRING_REGEX, ''),
                            args = qs ? app.unpackFromQueryString(qs) : {},
                            sessionStorageKey = 'gridURL_' + gridName,
                            storageUrl = sessionStorage ? sessionStorage.getItem(sessionStorageKey) : null;
                        if (!encodedStateData && storageUrl) {
                            this.encodedStateData = storageUrl;
                            this.skipAjaxCall = false;
                        } else if (!this.encodedStateData) {
                            this.encodedStateData = '';
                        }
                        if (args.dataLocale) {
                            this.encodedStateData += (this.encodedStateData ? '&' : '') +
                                    'dataLocale=' + args.dataLocale;
                            sessionStorage.setItem(sessionStorageKey, this.encodedStateData);
                            this.skipAjaxCall = false;
                        }
                        if (!this.skipAjaxCall) {
                            this.navigate('url=' + this.url.split('?').shift() + '|g/' + this.encodedStateData, { trigger: false, replace: true});
                        }
                    }
                    if (!this.skipAjaxCall) {
                        this.loadPage();
                    }
                    this.skipAjaxCall = false;
                },
                /**
                 * Adds a flash message to be displayed on next page load
                 * @see oro/messenger
                 */
                addFlashMessage: function() {
                    flashMessages.push(arguments);
                },
                /**
                 * @inheritdoc
                 */
                afterRequest: function() {
                    var message;
                    parent.afterRequest.call(this);
                    while (message = flashMessages.shift()) {
                        messenger.notificationFlashMessage.apply(messenger, message);
                    }
                }
            });
        /**
         * @inheritdoc
         */
        Navigation.getInstance = function() {
            return instance;
        };
        /**
         * @inheritdoc
         */
        Navigation.setup = function(options) {
            instance = new Navigation(options);
        };
        return Navigation;
    }
);

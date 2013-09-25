/**
 * Extends Oro Navigation to automatically navigate to last filter status
 *
 * @param {type} OroNavigation
 * @returns {unresolved}
 */
define(
    ['oro/navigation-orig', 'oro/app', 'oro/messenger'],
    function(OroNavigation, app, messenger) {

        var GRID_URL_REGEX = /enrich\/product\/(\?.*)?$/,
            QUERY_STRING_REGEX = /^[^\?]+\??/,
            flashMessages = [],
            parent = OroNavigation.prototype,
            instance,
            Navigation = OroNavigation.extend({
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
                    if (this.url.match(GRID_URL_REGEX)) {
                        var qs = this.url.replace(QUERY_STRING_REGEX, ''),
                            args = qs ? app.unpackFromQueryString(qs) : {};
                        if (!encodedStateData && sessionStorage && sessionStorage.gridURL_products) {
                            this.encodedStateData = sessionStorage.gridURL_products;
                            this.skipAjaxCall = false;
                        } else if (!this.encodedStateData) {
                            this.encodedStateData = "";
                        }
                        if (args.dataLocale) {
                            this.encodedStateData += (this.encodedStateData ? '&' : '') +
                                    'dataLocale=' + args.dataLocale;
                            sessionStorage.gridURL_products = this.encodedStateData;
                            this.skipAjaxCall = false;
                        }
                        if (!this.skipAjaxCall) {
                            this.navigate("url=" + this.url.split("?").shift() + "|g/" + this.encodedStateData, { trigger: false, replace: true});
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
                    parent.afterRequest.call(this)
                    while (message = flashMessages.shift()) {
                        messenger.notificationFlashMessage.apply(messenger, message)
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

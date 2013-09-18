/**
 * Extends Oro Navigation to automatically navigate to last filter status
 * 
 * @param {type} OroNavigation
 * @returns {unresolved}
 */
define(
    ['oro/navigation-orig', 'oro/app'],
    function(OroNavigation, app) {
        
        var GRID_URL_REGEX = /enrich\/product\/(\?.*)?$/,
            QUERY_STRING_REGEX = /^[^\?]+\??/,
            instance,
            Navigation = OroNavigation.extend({
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

                    this.init();

                    Backbone.Router.prototype.initialize.apply(this, arguments);
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
                    if (this.url.match(GRID_URL_REGEX)) {
                        var qs = this.url.replace(QUERY_STRING_REGEX, ''),
                            args = qs ? app.unpackFromQueryString(qs) : {}
                        if (!encodedStateData && sessionStorage && sessionStorage.gridURL_products) {
                            this.encodedStateData = sessionStorage.gridURL_products
                        } else if (!this.encodedStateData) {
                            this.encodedStateData = ""
                        }
                        if (args.dataLocale) {
                            this.encodedStateData += (this.encodedStateData ? '&' : '') +
                                    'dataLocale=' + args.dataLocale;
                            sessionStorage.gridURL_products = this.encodedStateData
                        }
                        this.navigate("url=" + this.url.split("?").shift() + "|g/" + this.encodedStateData, { trigger: false, replace: true});
                    }
                    if (!this.skipAjaxCall) {
                        this.loadPage();
                    }
                    this.skipAjaxCall = false;
                }});
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

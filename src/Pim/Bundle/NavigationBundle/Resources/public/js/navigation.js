/**
 * Extends Oro Navigation to automatically navigate to last filter status
 * 
 * @param {type} OroNavigation
 * @returns {unresolved}
 */
define(
    ['oro/navigation-orig'],
    function(OroNavigation) {
        
        var GRID_URL_REGEX = /enrich\/product\/(\?.*)$/,
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
                    if (this.url.match(GRID_URL_REGEX) && !encodedStateData && sessionStorage && sessionStorage.gridURL_products) {
                        this.navigate(sessionStorage.gridURL_products, { trigger: false, replace: true});
                        this.encodedStateData = sessionStorage.gridURL_products;
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

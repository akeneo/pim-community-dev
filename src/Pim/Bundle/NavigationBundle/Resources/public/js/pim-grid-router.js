/**
 * Saves filter status
 */
;require(
    ['oro/datagrid/router', 'oro/navigation'],
    function(Router, Navigation) {
        Router.prototype._handleStateChange = function(collection, options) {
            options = options || {};
            if (options.ignoreSaveStateInUrl) {
                return;
            }
            var encodedStateData = collection.encodeStateData(collection.state),
                url = '',
                navigation = Navigation.getInstance();
            if (navigation) {
                url = 'url=' + navigation.getHashUrl() + '|g/' + encodedStateData;
            } else {
                url = 'g/' + encodedStateData;
            }
            if (sessionStorage) {
                sessionStorage['gridURL_' + collection.inputName] = url;
            }
            this.navigate(url);
        }
        var navigation  = Navigation.getInstance()
        navigation.route(
            "(url=*page)(|g/*encodedStateData)",
            "defaultAction",
            function(page, encodedStateData) {
                navigation.beforeDefaultAction();
                navigation.encodedStateData = encodedStateData;
                navigation.url = page;
                if (!navigation.url) {
                    navigation.url = window.location.href.replace(navigation.baseUrl, '');
                }
                if (navigation.url.match(/enrich\/product/) && !encodedStateData && sessionStorage && sessionStorage.gridURL_products) {
                    navigation.navigate(sessionStorage.gridURL_products, { trigger: false, replace: true});
                    navigation.encodedStateData = sessionStorage.gridURL_products;
                }
                if (!navigation.skipAjaxCall) {
                    navigation.loadPage();
                }
                navigation.skipAjaxCall = false;
            }
        )
    }
);
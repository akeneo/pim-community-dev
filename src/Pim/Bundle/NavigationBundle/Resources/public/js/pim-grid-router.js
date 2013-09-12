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
            sessionStorage['gridURL_' + collection.inputName] = url
            this.navigate(url);
        }
    }
);
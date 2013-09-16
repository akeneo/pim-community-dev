/**
 * Extends Oro Grid Router to save current grid filter state.
 */
define(
    ['oro/datagrid/router-orig', 'oro/navigation'],
    function(Router, Navigation) {
        return Router.extend({
            _handleStateChange : function(collection, options) {
                options = options || {};
                var encodedStateData = collection.encodeStateData(collection.state),
                    url = '',
                    navigation = Navigation.getInstance()

                if (options.ignoreSaveStateInUrl) {
                    return;
                }

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
        });
    }
);
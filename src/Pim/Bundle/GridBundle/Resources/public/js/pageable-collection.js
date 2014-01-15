/**
 * Extends Oro Pageable Collection to add category management
 *
 * @param {type} OroPageableCollection
 * @param {type} app
 * @returns {unresolved}
 */
define(
    ['oro/pageable-collection', 'oro/app', 'underscore'],
    function(OroPageableCollection, app, _){
        var parent = OroPageableCollection.prototype,
            PageableCollection = OroPageableCollection.extend({
                /**
                 * @inheritdoc
                 */
                encodeStateData: function(stateObject) {
                    var encodedStateData = parent.encodeStateData.call(this, stateObject);
                    if (stateObject.dataLocale) {
                        encodedStateData += '&dataLocale=' + stateObject.dataLocale;
                    }
                    if ('&' === encodedStateData[0]) {
                        encodedStateData = encodedStateData.substr(1);
                    }
                    return encodedStateData;
                },
                /**
                 * @inheritdoc
                 */
                decodeStateData: function(stateString) {
                    var QSData = app.unpackFromQueryString(stateString),
                        data = app.invertKeys(QSData, _.invert(this.stateShortKeys));
                    if (QSData.dataLocale) {
                        data.dataLocale = QSData.dataLocale;
                    }
                    return data;
                },
                /**
                 * @inheritdoc
                 */
                processFiltersParams: function(data, state) {
                    if (!state) {
                        state = this.state;
                    }
                    var queryParams = parent.processFiltersParams.call(this, data, state);
                    if (state.dataLocale) {
                        queryParams.dataLocale = state.dataLocale;
                    }
                    return queryParams;
                },
                /**
                * Clone collection
                *
                * @return {PageableCollection}
                */
               clone: function() {
                   var collectionOptions = {};
                   collectionOptions.url = this.url;
                   collectionOptions.inputName = this.inputName;
                   var newCollection = new PageableCollection(this.toJSON(), collectionOptions);
                   newCollection.state = app.deepClone(this.state);
                   return newCollection;
               }
            });
        return PageableCollection;
    }
);

/**
 * Extends Oro Pageable Collection to add category management
 * 
 * @param {type} OroPageableCollection
 * @param {type} app
 * @returns {unresolved}
 */
define(
    ["oro/pageable-collection-orig", "oro/app", "underscore"], 
    function(OroPageableCollection, app, _){
        var parent = OroPageableCollection.prototype,
            TREE_REGEX = /(&?treeId=(\d+))/,
            CATEGORY_REGEX = /(&?categoryId=(\d+))/,
            PageableCollection = OroPageableCollection.extend({
                /**
                 * @inheritdoc
                 */
                state: _.extend(OroPageableCollection.prototype.state, {
                    categoryId: '',
                    treeId: ''
                }),
                /**
                 * Sets the category for the collection
                 * 
                 * @param {int} treeId
                 * @param {int} categoryId
                 */
                setCategory: function(treeId, categoryId) {
                    treeId = (categoryId === '') ? '' : treeId;
                    if (treeId !== this.state.treeId || categoryId !== this.state.categoryId) {
                        this.updateState({ treeId: treeId, categoryId: categoryId })
                        this.url = this.setCategoryInUrl(this.url)
                        return true;
                    } else {
                        return false;
                    }
                },
                setCategoryInUrl: function(url) {
                    url = url.replace(CATEGORY_REGEX, '').replace(TREE_REGEX, '');
                    var qs = app.packToQueryString({
                                categoryId: this.state.categoryId,
                                treeId: this.state.treeId
                            });
                    if ("?" === _.last(url)) {
                        url += qs
                    } else if (-1 === url.indexOf("?")) {
                        url += "?" + qs;
                    } else {
                        url += "&" + qs;
                    }
                    return url;
                },
                /**
                 * @inheritdoc
                 */
                encodeStateData: function(stateObject) {
                    var encodedStateData = parent.encodeStateData.call(this, stateObject)
                    if (stateObject.treeId) {
                        encodedStateData += "&treeId=" + stateObject.treeId;
                    }
                    if (stateObject.categoryId) {
                        encodedStateData += "&categoryId=" + stateObject.categoryId;
                    }
                    if (stateObject.dataLocale) {
                        encodedStateData += "&dataLocale=" + stateObject.dataLocale;
                    }
                    if ("&" === encodedStateData[0]) {
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
                    if (QSData.treeId) {
                        data.treeId = QSData.treeId;
                    }
                    if (QSData.categoryId) {
                        data.categoryId = QSData.categoryId;
                    }
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
                    var queryParams = parent.processFiltersParams.call(this, data, state)
                    if (state.categoryId) {
                        queryParams.categoryId = state.categoryId;
                    }
                    if (state.treeId) {
                        queryParams.treeId = state.treeId;
                    }
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
    
)
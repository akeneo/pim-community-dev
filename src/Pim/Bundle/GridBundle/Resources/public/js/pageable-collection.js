/**
 * Extends Oro Pageable Collection to add category management
 * 
 * @param {type} OroPageableCollection
 * @param {type} app
 * @returns {unresolved}
 */
define(
    ["oro/pageable-collection-orig", "oro/app"], 
    function(OroPageableCollection, app){
        var parent = OroPageableCollection.prototype,
            treePattern = /(&treeId=(\d+))/,
            categoryPattern = /(&categoryId=(\d+))/,
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
                    var treeString = this.state.categoryId === '' ? '' : '&treeId=' + this.state.treeId,
                        categoryString = this.state.categoryId === '' ? '' : '&categoryId=' + this.state.categoryId;

                    if (url.match(treePattern)) {
                        url = url.replace(treePattern, treeString);
                    } else {
                        url += treeString;
                    }

                    if (url.match(categoryPattern)) {
                        url = url.replace(categoryPattern, categoryString);
                    } else {
                        url += categoryString;
                    }
                    return url
                },
                /**
                 * @inheritdoc
                 */
                encodeStateData: function(stateObject) {
                    var encodedStateData = parent.encodeStateData.call(this, stateObject);
                    if (stateObject.treeId) {
                        encodedStateData += "&treeId=" + stateObject.treeId;
                    }
                    if (stateObject.categoryId) {
                        encodedStateData += "&categoryId=" + stateObject.categoryId;
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
                    return queryParams;
                }
            });
        return PageableCollection;
    }
)
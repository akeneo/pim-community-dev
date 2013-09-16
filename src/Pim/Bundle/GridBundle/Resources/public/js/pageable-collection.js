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
            PageableCollection = OroPageableCollection.extend({
        
                /**
                 * Sets the category for the collection
                 * @param {int} treeId
                 * @param {int} categoryId
                 */
                setCategory: function(treeId, categoryId) {
                    this.state.treeId = treeId;
                    this.state.categoryId = categoryId;
                    var treePattern = /(&treeId=(\d+))/,
                        nodePattern = /(&categoryId=(\d+))/,
                        treeString = categoryId === '' ? '' : '&treeId=' + treeId,
                        categoryString = categoryId === '' ? '' : '&categoryId=' + categoryId;

                    if (this.url.match(treePattern)) {
                        this.url = this.url.replace(treePattern, treeString);
                    } else {
                        this.url += treeString;
                    }

                    if (this.url.match(nodePattern)) {
                        this.url = this.url.replace(nodePattern, categoryString);
                    } else {
                        this.url += categoryString;
                    }
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
                processQueryParams: function(data, state) {
                    var queryParams = parent.processQueryParams.call(this, data, state)
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
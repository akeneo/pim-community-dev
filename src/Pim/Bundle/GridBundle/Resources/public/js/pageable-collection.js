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
                 * @return {boolean} true if the category was changed
                 */
                setCategory: function(treeId, categoryId) {
                    treeId = (categoryId === '') ? '' : treeId;
                    if (treeId !== this.state.treeId || categoryId !== this.state.categoryId) {
                        this.state.categoryId = categoryId;
                        this.state.treeId = treeId;
                        var treePattern = /(&treeId=(\d+))/,
                            categoryPattern = /(&categoryId=(\d+))/,
                            treeString = categoryId === '' ? '' : '&treeId=' + treeId,
                            categoryString = categoryId === '' ? '' : '&categoryId=' + categoryId;

                        if (this.url.match(treePattern)) {
                            this.url = this.url.replace(treePattern, treeString);
                        } else {
                            this.url += treeString;
                        }

                        if (this.url.match(categoryPattern)) {
                            this.url = this.url.replace(categoryPattern, categoryString);
                        } else {
                            this.url += categoryString;
                        }
                        return true;
                    } else {
                        return false;
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
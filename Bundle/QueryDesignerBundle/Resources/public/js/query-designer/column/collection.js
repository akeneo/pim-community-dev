/* global define */
define(['backbone', 'oro/query-designer/column/model'],
function(Backbone, ColumnModel) {
    'use strict';

    /**
     * @export  oro/query-designer/column/collection
     * @class   oro.queryDesigner.column.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        model: ColumnModel
    });
});

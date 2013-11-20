/* global define */
define(['backbone', 'oro/query-designer/filter/model'],
function(Backbone, FilterModel) {
    'use strict';

    /**
     * @export  oro/query-designer/filter/collection
     * @class   oro.queryDesigner.filter.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        model: FilterModel
    });
});

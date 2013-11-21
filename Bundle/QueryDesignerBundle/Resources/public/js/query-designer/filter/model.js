/* global define */
define(['backbone'],
function(Backbone) {
    'use strict';

    /**
     * @export  oro/query-designer/filter/model
     * @class   oro.queryDesigner.filter.Model
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        defaults: {
            id : null,
            index : null,
            columnName : null,
            criterion: null
        }
    });
});

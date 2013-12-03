/* global define */
define(['backbone', 'oro/app'],
function(Backbone, app) {
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
        },

        toJSON: function(options) {
            return app.deepClone(this.attributes);
        }
    });
});

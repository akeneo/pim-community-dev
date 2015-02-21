define(['backbone', 'oro/datagrid/grid-views/model'],
    function (Backbone, GridViewsModel) {
        'use strict';

        return Backbone.Collection.extend({
            /** @property */
            model: GridViewsModel
        });
    }
);

define(['backbone', 'oro/grid/grid-views/model'],
    function (Backbone, GridViewsModel) {
        'use strict';

        return Backbone.Collection.extend({
            /** @property */
            model: GridViewsModel
        });
    }
);

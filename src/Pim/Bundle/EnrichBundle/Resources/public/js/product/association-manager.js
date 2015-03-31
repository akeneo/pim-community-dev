'use strict';

define(['jquery', 'underscore', 'routing'], function ($, _, Routing) {
    return {
        promise: null,
        getAssociationTypes: function ()
        {
            if (null !== this.promise) {
                return this.promise.promise();
            }

            this.promise = $.Deferred();

            $.getJSON(
                Routing.generate('oro_datagrid_index', { gridName: 'association-type-grid' })
            ).done(_.bind(function (data) {
                this.promise.resolve(data.data);
            }, this));

            return this.promise.promise();
        }
    };
});

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

            $.getJSON(Routing.generate('pim_enrich_association_type_rest_index')).done(_.bind(function (data) {
                this.promise.resolve(data);
            }, this));

            return this.promise.promise();
        }
    };
});

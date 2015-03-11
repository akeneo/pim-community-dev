"use strict";

define(['routing'], function (Routing) {
    return {
        fields: {},
        attributeGroups: null,
        attributeGroupsPromise: null,
        getAttributeGroups: function()
        {
            var promise = new $.Deferred();

            //If we never called the backend we call it and set the promise
            if (null === this.attributeGroupsPromise) {
                this.attributeGroupsPromise = $.ajax(
                    Routing.generate('pim_enrich_attributegroup_rest_index'),
                    {
                        method: 'GET'
                    }
                ).promise();
            }

            //If attributeGroups are not initialized we have to wait for the promise to be resolved
            //and if not we directly resolve
            if (null === this.attributeGroups) {
                this.attributeGroupsPromise.done(_.bind(function(data) {
                    this.attributeGroups = data;

                    promise.resolve(this.attributeGroups);
                }, this));
            } else {
                promise.resolve(this.attributeGroups);
            }

            return promise.promise();
        }
    };
});

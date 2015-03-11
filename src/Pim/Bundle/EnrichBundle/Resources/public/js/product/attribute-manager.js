"use strict";

define(['routing'], function (Routing) {
    return {
        attributes: null,
        attributesPromise: null,
        getAttribute: function(attributeCode)
        {
            var promise = new $.Deferred();

            this.getAttributes().done(function(attributes) {
                promise.resolve(attributes[attributeCode]);
            });

            return promise.promise();
        },
        getAttributes: function()
        {
            var promise = new $.Deferred();

            //If we never called the backend we call it and set the promise
            if (null === this.attributesPromise) {
                this.attributesPromise = $.ajax(
                    Routing.generate('pim_enrich_attribute_rest_index'),
                    {
                        method: 'GET'
                    }
                ).promise();
            }

            //If attributes are not initialized we have to wait for the promise to be resolved
            //and if not we directly resolve
            if (null === this.attributes) {
                this.attributesPromise.done(_.bind(function(data) {
                    this.attributes = data;

                    promise.resolve(this.attributes);
                }, this));
            } else {
                promise.resolve(this.attributes);
            }

            return promise.promise();
        }
    };
});

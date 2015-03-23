"use strict";

define(['routing'], function (Routing) {
    return {
        entities: {},
        promises: {},
        urls: {
            'attributegroups': 'pim_enrich_attributegroup_rest_index',
            'attributes': 'pim_enrich_attribute_rest_index',
            'families': 'pim_enrich_family_rest_index',
            'channels': 'pim_enrich_channel_rest_index',
            'locales': 'pim_enrich_locale_rest_index',
            'measures': 'pim_enrich_measures_rest_index',
            'currencies': 'pim_enrich_currency_rest_index'
        },
        getEntityList: function(entityType)
        {
            var promise = $.Deferred();

            //If we never called the backend we call it and set the promise
            if (!(entityType in this.promises)) {
                this.promises[entityType] = $.ajax(
                    Routing.generate(this.urls[entityType]),
                    {
                        method: 'GET'
                    }
                ).promise();
            }

            //If entities are not initialized we have to wait for the promise to be resolved
            //and if not we directly resolve
            if (!(entityType in this.entities)) {
                this.promises[entityType].done(_.bind(function(data) {
                    this.entities[entityType] = data;

                    promise.resolve(this.entities[entityType]);
                }, this));
            } else {
                promise.resolve(this.entities[entityType]);
            }

            return promise.promise();
        },
        getEntity: function(entityType, entityIdentifier)
        {
            var promise = $.Deferred();

            this.getEntityList(entityType).done(function(entities) {
                promise.resolve(entities[entityIdentifier]);
            });

            return promise.promise();
        },
        getConfig: function()
        {
            var promise = $.Deferred();

            $.when(
                this.getEntityList('attributegroups'),
                this.getEntityList('attributes'),
                this.getEntityList('channels'),
                this.getEntityList('locales'),
                this.getEntityList('measures'),
                this.getEntityList('currencies'),
                this.getEntityList('families')
            ).done(_.bind(function() {
                promise.resolve(this.entities);
            }, this));

            return promise.promise();
        }
    };
});

"use strict";

define(['routing'], function (Routing) {
    return {
        entities: {
            'locales': [
                {
                    'code': 'fr_FR',
                    'label': 'French'
                },
                {
                    'code': 'en_US',
                    'label': 'English'
                }
            ]
        },
        promises: {},
        urls: {
            'attributegroups': 'pim_enrich_attributegroup_rest_index',
            'attributes': 'pim_enrich_attribute_rest_index',
            'families': 'pim_enrich_family_rest_index',
            'channels': 'pim_enrich_channel_rest_index',
            'measures': 'pim_enrich_measures_rest_index'
        },
        getEntityList: function(entityType)
        {
            var promise = new $.Deferred();

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
            var promise = new $.Deferred();

            this.getEntityList(entityType).done(function(entities) {
                promise.resolve(entities[entityIdentifier]);
            });

            return promise.promise();
        },
        getConfig: function()
        {
            var promise = new $.Deferred();

            $.when(
                this.getEntityList('attributegroups'),
                this.getEntityList('attributes'),
                this.getEntityList('channels'),
                this.getEntityList('measures')
            ).done(_.bind(function() {
                promise.resolve(this.entities);
            }, this));

            return promise.promise();
        }
    };
});

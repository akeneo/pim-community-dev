'use strict';

define(['jquery', 'underscore', 'routing'], function ($, _, Routing) {
    return {
        promises: {},
        urls: {
            'attributegroups': 'pim_enrich_attributegroup_rest_index',
            'attributes':      'pim_enrich_attribute_rest_index',
            'families':        'pim_enrich_family_rest_index',
            'channels':        'pim_enrich_channel_rest_index',
            'locales':         'pim_enrich_locale_rest_index',
            'measures':        'pim_enrich_measures_rest_index',
            'currencies':      'pim_enrich_currency_rest_index'
        },
        getEntityList: function (entityType) {
            // If we never called the backend we call it and store the promise
            if (!(entityType in this.promises)) {
                this.promises[entityType] = $.getJSON(
                    Routing.generate(this.urls[entityType])
                ).then(_.identity).promise();
            }

            return this.promises[entityType];
        },
        getEntity: function (entityType, entityIdentifier) {
            var promise = $.Deferred();

            this.getEntityList(entityType).done(function (entities) {
                promise.resolve(_.findWhere(entities, {code: entityIdentifier}));
            });

            return promise.promise();
        }
    };
});

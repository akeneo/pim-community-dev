'use strict';

define(['jquery', 'underscore', 'backbone', 'routing'], function ($, _, Backbone, Routing) {
    var EntityRepository = function (options) {
        var entityListPromise = null;
        var entityPromises = {};
        this.options = options || {};

        this.findAll = function () {
            if (!entityListPromise) {
                entityListPromise = $.getJSON(
                    Routing.generate(this.options.urls.list)
                ).then(_.identity).promise();
            }

            return entityListPromise;
        };

        this.find = function (identifier) {
            if (!(identifier in entityPromises)) {
                var deferred = $.Deferred();

                if (this.options.urls.get) {
                    $.getJSON(
                        Routing.generate(this.options.urls.get, { identifier: identifier })
                    ).then(_.identity).done(function (entity) {
                        deferred.resolve(entity);
                    }).fail(deferred.reject);
                } else {
                    this.findAll().done(function (entities) {
                        var entity = _.findWhere(entities, {code: identifier});
                        if (entity) {
                            deferred.resolve(entity);
                        } else {
                            deferred.reject();
                        }
                    });
                }

                entityPromises[identifier] = deferred.promise();
            }

            return entityPromises[identifier];
        };

        this.clear = function (entityId) {
            if (entityId) {
                delete entityPromises[entityId];
            } else {
                entityListPromise = null;
            }
        };

        return this;
    };

    // Provide a Backbone-like extension point
    EntityRepository.extend = Backbone.Model.extend;

    return EntityRepository;
});

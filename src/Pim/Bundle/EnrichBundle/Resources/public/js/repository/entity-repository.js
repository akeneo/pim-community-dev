'use strict';

define(['jquery', 'underscore', 'backbone', 'routing'], function ($, _, Backbone, Routing) {
    return Backbone.Model.extend({
        entityListPromise: null,
        entityPromises: {},
        initialize: function (options) {
            this.entityListPromise = null;
            this.entityPromises = {};
            this.options = options || {};
        },
        findAll: function () {
            if (!this.entityListPromise) {
                this.entityListPromise = $.getJSON(
                    Routing.generate(this.options.urls.list)
                ).then(_.identity).promise();
            }

            return this.entityListPromise;
        },
        find: function (identifier) {
            if (!(identifier in this.entityPromises)) {
                var deferred = $.Deferred();

                if (this.options.urls.get) {
                    $.getJSON(
                        Routing.generate(this.options.urls.get, { identifier: identifier })
                    ).then(_.identity).done(function (entity) {
                        deferred.resolve(entity);
                    }).fail(function () {
                        console.log(arguments);

                        return deferred.reject();
                    });
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

                this.entityPromises[identifier] = deferred.promise();
            }

            return this.entityPromises[identifier];
        },
        clear: function (entityId) {
            if (entityId) {
                delete this.entityPromises[entityId];
            } else {
                this.entityListPromise = null;
            }
        }
    });
});

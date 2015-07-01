/* global console */
'use strict';

define(['jquery', 'underscore', 'backbone', 'routing'], function ($, _, Backbone, Routing) {
    var getObjects = function (promises) {
        return $.when.apply($, _.toArray(promises)).then(function () {
            return 0 !== arguments.length ? _.toArray(arguments) : [];
        });
    }

    return Backbone.Model.extend({
        entityListPromise: null,
        entityPromises: {},
        /**
         * @param Array options
         */
        initialize: function (options) {
            this.entityListPromise = null;
            this.entityPromises = {};
            this.options = options || {};
        },
        /**
         * Fetch all elements of the collection
         *
         * @return Promise
         */
        fetchAll: function () {
            if (!this.entityListPromise) {
                this.entityListPromise = $.getJSON(
                    Routing.generate(this.options.urls.list)
                ).then(_.identity).promise();
            }

            return this.entityListPromise;
        },
        /**
         * Fetch an element based on its identifier
         *
         * @param String identifier
         *
         * @return Promise
         */
        fetch: function (identifier) {
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
                    this.fetchAll().done(function (entities) {
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
        /**
         * Fetch all entities for the given identifiers
         *
         * @param Array identifiers
         *
         * @return Promise
         */
        fetchByIdentifiers: function (identifiers) {
            _.each(identifiers, _.bind(function (identifier) {
                if (identifier in this.entityPromises) {
                    identifiers = _.without(identifiers, identifier);
                }
            }, this));

            if (0 === identifiers.length) {
                return getObjects(this.entityPromises);
            }

            return $.when(
                    $.getJSON(Routing.generate(this.options.urls.list, { identifiers: identifiers.join(',') }))
                        .then(_.identity),
                    this.getIdentifierField()
                )
                .then(_.bind(function (entities, identifierCode) {
                    _.each(entities, _.bind(function (entity) {
                        this.entityPromises[entity[identifierCode]] = $.Deferred().resolve(entity);
                    }, this));

                    return getObjects(this.entityPromises);
                }, this));

            return deferred;
        },
        /**
         * Get the identifier attribute of the collection
         *
         * @return Promise
         */
        getIdentifierField: function () {
            return $.Deferred().resolve('code');
        },
        /**
         * Clear cache of the fetcher
         *
         * @param String|null identifier
         */
        clear: function (identifier) {
            if (identifier) {
                delete this.entityPromises[identifier];
            } else {
                this.entityListPromise = null;
            }
        }
    });
});

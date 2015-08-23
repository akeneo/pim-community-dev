/* global console */
'use strict';

define(['jquery', 'underscore', 'backbone', 'routing'], function ($, _, Backbone, Routing) {
    var getObjects = function (promises) {
        return $.when.apply($, _.toArray(promises)).then(function () {
            return 0 !== arguments.length ? _.toArray(arguments) : [];
        });
    };

    return Backbone.Model.extend({
        entityListPromise: null,
        entityPromises: {},

        /**
         * @param {Array} options
         */
        initialize: function (options) {
            this.entityListPromise = null;
            this.entityPromises = {};
            this.options = options || {};
        },

        /**
         * Fetch all elements of the collection
         *
         * @return {Promise}
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
         * @param {string} identifier
         *
         * @return {Promise}
         */
        fetch: function (identifier, options) {
            options = options || {};

            if (!(identifier in this.entityPromises)) {
                var deferred = $.Deferred();

                if (this.options.urls.get) {
                    $.getJSON(
                        Routing.generate(this.options.urls.get, _.extend({identifier: identifier}, options))
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
         * @param {Array} identifiers
         *
         * @return {Promise}
         */
        fetchByIdentifiers: function (identifiers) {
            _.each(identifiers, function (identifier) {
                if (identifier in this.entityPromises) {
                    identifiers = _.without(identifiers, identifier);
                }
            }.bind(this));

            if (0 === identifiers.length) {
                return getObjects(this.entityPromises);
            }

            return $.when(
                    $.getJSON(Routing.generate(this.options.urls.list, { identifiers: identifiers.join(',') }))
                        .then(_.identity),
                    this.getIdentifierField()
                )
                .then(function (entities, identifierCode) {
                    _.each(entities, function (entity) {
                        this.entityPromises[entity[identifierCode]] = $.Deferred().resolve(entity);
                    }.bind(this));

                    return getObjects(this.entityPromises);
                }.bind(this));
        },

        /**
         * Get the identifier attribute of the collection
         *
         * @return {Promise}
         */
        getIdentifierField: function () {
            return $.Deferred().resolve('code');
        },

        /**
         * Clear cache of the fetcher
         *
         * @param {string}|{null} identifier
         */
        clear: function (identifier) {
            if (identifier) {
                delete this.entityPromises[identifier];
            } else {
                this.entityListPromise = null;
                this.entityPromises    = {};
            }
        }
    });
});

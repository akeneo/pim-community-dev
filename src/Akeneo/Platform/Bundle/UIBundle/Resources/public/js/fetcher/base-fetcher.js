/* global console */
'use strict';

define(['jquery', 'underscore', 'backbone', 'routing'], function ($, _, Backbone, Routing) {
    return Backbone.Model.extend({
        entityListPromise: null,
        entityPromises: {},

        /**
         * @param {Object} options
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
        fetchAll: function (searchOptions) {
            if (!this.entityListPromise) {
                if (!_.has(this.options.urls, 'list')) {
                    return $.Deferred().reject().promise();
                }

                this.entityListPromise = $.getJSON(
                    Routing.generate(this.options.urls.list, searchOptions),
                ).then(_.identity).promise();
            }

            return this.entityListPromise;
        },

        /**
         * Search elements of the collection
         *
         * @return {Promise}
         */
        search: function (searchOptions) {
            var url = '';
            if (!_.has(this.options.urls, 'search')) {
                if (!_.has(this.options.urls, 'list')) {
                    return $.Deferred().reject().promise();
                } else {
                    url = this.options.urls.list;
                }
            } else {
                url = this.options.urls.search;
            }

            return this.getJSON(url, searchOptions).then(_.identity).promise();
        },

        /**
         * Fetch an element based on its identifier
         *
         * @param {string} identifier
         * @param {Object} options
         *
         * @return {Promise}
         */
        fetch: function (identifier, options) {
            options = options || {};

            if (!(identifier in this.entityPromises) || false === options.cached) {
                var deferred = $.Deferred();

                if (this.options.urls.get && !options.force_list_method) {
                    $.getJSON(
                        Routing.generate(this.options.urls.get, _.extend({identifier: identifier}, options))
                    ).then(_.identity).done(function (entity) {
                        deferred.resolve(entity);
                    }).fail(function (promise, status, error) {
                        console.error('Error during fetching: ', error);

                        return deferred.reject(promise);
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
        fetchByIdentifiers: function (identifiers, options) {
            options = options || {};
            if (0 === identifiers.length) {
                return $.Deferred().resolve([]).promise();
            }

            var uncachedIdentifiers = _.difference(identifiers, _.keys(this.entityPromises));
            if (0 === uncachedIdentifiers.length) {
                return this.getObjects(_.pick(this.entityPromises, identifiers));
            }

            return $.when(
                this.getJSON(
                    this.options.urls.list,
                    _.extend({identifiers: uncachedIdentifiers.join(',')}, options)
                ).then(_.identity),
                this.getIdentifierField()
            ).then(function (entities, identifierCode) {
                _.each(entities, function (entity) {
                    this.entityPromises[entity[identifierCode]] = $.Deferred().resolve(entity).promise();
                }.bind(this));

                return this.getObjects(_.pick(this.entityPromises, identifiers));
            }.bind(this));
        },

        /**
         * Get the list of elements in JSON format.
         *
         * @param {string} url
         * @param {Object} parameters
         *
         * @returns {Promise}
         */
        getJSON: function (url, parameters) {
            return $.getJSON(Routing.generate(url, parameters));
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
         * @param {string|null} identifier
         */
        clear: function (identifier) {
            if (identifier) {
                delete this.entityPromises[identifier];
            } else {
                this.entityListPromise = null;
                this.entityPromises = {};
            }
        },

        /**
         * Wait for promises to resolve and return the promises results wrapped in a Promise
         *
         * @param {Array|Object} promises
         *
         * @return {Promise}
         */
        getObjects: function (promises) {
            return $.when.apply($, _.toArray(promises)).then(function () {
                return 0 !== arguments.length ? _.toArray(arguments) : [];
            });
        }
    });
});

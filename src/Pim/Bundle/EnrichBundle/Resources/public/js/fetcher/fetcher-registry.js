'use strict';

define(['module', 'jquery', 'underscore'], function (module, $, _) {
    return {
        fetchers: {},
        initializePromise: null,

        /**
         * @return Promise
         */
        initialize: function () {
            if (null === this.initializePromise) {
                var deferred = $.Deferred();
                var fetchers = {};

                _.each(module.config().fetchers, function (config, name) {
                    config = _.isString(config) ? { module: config } : config;
                    config.options = config.options || {};
                    fetchers[name] = config;
                });

                require(_.pluck(fetchers, 'module'), function () {
                    _.each(fetchers, function (fetcher) {
                        fetcher.loadedModule = new (require(fetcher.module))(fetcher.options);
                    });

                    this.fetchers = fetchers;
                    deferred.resolve();
                }.bind(this));

                this.initializePromise = deferred.promise();
            }

            return this.initializePromise;
        },

        /**
         * Get the related fetcher for the given collection name
         *
         * @param {String} entityType
         *
         * @return Fetcher
         */
        getFetcher: function (entityType) {
            return (this.fetchers[entityType] || this.fetchers.default).loadedModule;
        },

        /**
         * Clear the fetcher cache for the given collection name
         *
         * @param {String}         entityType
         * @param {String|integer} entity
         */
        clear: function (entityType, entity) {
            return this.getFetcher(entityType).clear(entity);
        },

        /**
         * Clear all fetchers cache
         */
        clearAll: function () {
            _.each(this.fetchers, function (fetcher) {
                fetcher.loadedModule.clear();
            });
        }
    };
});

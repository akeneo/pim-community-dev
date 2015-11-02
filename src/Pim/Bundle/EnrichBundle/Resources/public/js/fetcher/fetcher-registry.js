'use strict';

define(['module', 'jquery', 'underscore'], function (module, $, _) {
    return {
        fetchers: {},
        initializePromise: null,
        warm: false,

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
         * Warm up the cache on first load
         */
        warmUp: function () {
            if (!this.warm) {
                _.each(this.fetchers, function (fetcher, code) {
                    this.getFetcher(code).fetchAll();
                }.bind(this));

                this.warm = true;
            }
        },

        /**
         * Get the related fetcher for the given collection name
         *
         * @param String entityType
         *
         * @return Fetcher
         */
        getFetcher: function (entityType) {
            return (this.fetchers[entityType] || this.fetchers['default']).loadedModule;
        },

        /**
         * Clear the fetcher cache for the given collection name
         *
         * @param String         entityType
         * @param String|Ingeter entity
         */
        clear: function (entityType, entity) {
            return this.getFetcher(entityType).clear(entity);
        },

        /**
         * Clear all fetchers cache
         */
        clearAll: function () {
            this.warm = false;
            _.each(this.fetchers, function (fetcher) {
                fetcher.loadedModule.clear();
            });
        }
    };
});

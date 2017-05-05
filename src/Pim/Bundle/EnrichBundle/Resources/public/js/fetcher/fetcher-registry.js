'use strict';

define([
    'module-config',
    'jquery',
    'underscore',
    'fetchers'
], function (
        module,
        $,
        _,
        fetcherMapping
    ) {
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
                var fetchers = module.config().fetchers || {}

                _.each(fetchers, function (config, name) {
                    config = _.isString(config) ? { module: config } : config;
                    config.options = config.options || {};
                    fetchers[name] = config;
                });

                _.each(fetchers, function (fetcher) {
                    var MatchedFetcher = (fetcherMapping[fetcher.module]);
                    if (MatchedFetcher) fetcher.loadedModule = new MatchedFetcher(fetcher.options)
                });


                this.fetchers = fetchers;
                deferred.resolve();


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

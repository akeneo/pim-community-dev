'use strict';

define(['module-config', 'jquery', 'underscore', 'pim/base-fetcher', 'fetcher-list', 'paths'], function (module, $, _, BaseFetcher, fetcherList, paths) {
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

                _.each(fetcherList, function (config, name) {
                    config = _.isString(config) ? { module: config } : config;
                    config.options = config.options || {};
                    fetchers[name] = config;
                });

                require.ensure([], function () {
                    for (var fetcher in fetcherList) {
                        var moduleName = paths[fetcherList[fetcher].module]
                        var requestFetcher = require.context('./src/Pim/Bundle', true, /^\.\/.*\.js$/)
                        var ResolvedModule = requestFetcher(moduleName);
                        fetchers[fetcher].loadedModule = new ResolvedModule(fetchers[fetcher].options)
                    }

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
            var fetcher = (this.fetchers[entityType] || this.fetchers.default)
            console.dir(this.fetchers)
            // console.log('fetcher', fetcher)
            // console.log('default', this.fetchers.default)

            return fetcher.loadedModule;
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

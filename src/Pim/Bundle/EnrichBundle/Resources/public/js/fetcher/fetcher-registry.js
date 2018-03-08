'use strict';

define(['jquery', 'underscore', 'pim/base-fetcher', 'require-context'],
function ($, _, BaseFetcher, requireContext) {
    const FetcherRegistry = {
        fetchers: {},
        initializePromise: null,

        /**
         * @return Promise
         */
        initialize: function () {
            if (null === this.initializePromise) {
                var fetcherList = __moduleConfig.fetchers
                var deferred = $.Deferred();
                var defaultFetcher = 'pim/base-fetcher'
                var fetchers = {};

                _.each(fetcherList, function (config, name) {
                    config = _.isString(config) ? { module: config } : config;
                    config.options = config.options || { };
                    fetchers[name] = config;
                });

                for (var fetcher in fetcherList) {
                    var moduleName = fetcherList[fetcher].module || defaultFetcher
                    var ResolvedModule = requireContext(moduleName);
                    if (ResolvedModule.default) {
                        ResolvedModule = ResolvedModule.default;
                    }
                    fetchers[fetcher].loadedModule = new ResolvedModule(fetchers[fetcher].options)
                    fetchers[fetcher].options = fetcherList[fetcher].options
                }

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
            var fetcher = (this.fetchers[entityType] || this.fetchers.default)

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

    return FetcherRegistry;
});

'use strict';

define([
    'module-config',
    'jquery',
    'underscore',
    'pim/attribute-fetcher',
    'pim/attribute-group-fetcher',
    'pim/datagrid-view-fetcher',
    'pim/base-fetcher',
    'pim/completeness-fetcher',
    'pim/locale-fetcher',
    'pim/product-fetcher',
    'pim/variant-group-fetcher',
    'pim/datagrid-view-fetcher'
], function (
        module,
        $,
        _,
        AttributeFetcher,
        AttributeGroupFetcher,
        BaseFetcher,
        CompletenessFetcher,
        LocaleFetcher,
        ProductFetcher,
        VariantGroupFetcher,
        DatagridViewFetcher
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

                // @TODO - burn this and use json instead
                var fetcherMapping = {
                    'pim/attribute-fetcher': AttributeFetcher,
                    'pim/attribute-group-fetcher': AttributeGroupFetcher,
                    'pim/base-fetcher': BaseFetcher,
                    'pim/completeness-fetcher': CompletenessFetcher,
                    'pim/locale-fetcher': LocaleFetcher,
                    'pim/product-fetcher': ProductFetcher,
                    'pim/variant-group-fetcher': VariantGroupFetcher,
                    'pim/datagrid-view-fetcher': DatagridViewFetcher
                };

                _.each(fetchers, function (config, name) {
                    config = _.isString(config) ? { module: config } : config;
                    config.options = config.options || {};
                    fetchers[name] = config;
                });

                _.each(fetchers, function (fetcher) {
                    fetcher.loadedModule = new (fetcherMapping[fetcher.module])(fetcher.options)
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

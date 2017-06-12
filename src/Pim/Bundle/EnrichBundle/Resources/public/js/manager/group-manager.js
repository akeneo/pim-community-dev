'use strict';

define(['jquery', 'underscore', 'pim/fetcher-registry'], function ($, _, FetcherRegistry) {
    return {
        getProductGroups: function (product) {
            var promises = _.map(product.groups, function (groupCode) {
                return FetcherRegistry.getFetcher('group').fetch(groupCode);
            });

            if (product.variant_group) {
                promises.push(FetcherRegistry.getFetcher('variant-group').fetch(product.variant_group));
            }

            return $.when.apply($, promises).then(function () {
                return _.toArray(arguments);
            });
        }
    };
});

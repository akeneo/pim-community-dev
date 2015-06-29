'use strict';

define(['underscore', 'pim/base-fetcher'], function (_, BaseFetcher) {
    return BaseFetcher.extend({
        getIdentifierField: function () {
            return this.fetchAll().then(function (attributes) {
                return _.findWhere(attributes, { type: 'pim_catalog_identifier' });
            }).promise();
        }
    });
});

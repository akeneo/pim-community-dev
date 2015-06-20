'use strict';

define(['underscore', 'pim/entity-repository'], function (_, EntityRepository) {
    return EntityRepository.extend({
        getIdentifier: function () {
            return this.findAll().then(function (attributes) {
                return _.findWhere(attributes, { type: 'pim_catalog_identifier' });
            }).promise();
        }
    });
});

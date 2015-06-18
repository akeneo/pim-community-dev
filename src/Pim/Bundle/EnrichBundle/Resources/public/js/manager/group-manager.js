'use strict';

define(['module', 'jquery', 'underscore', 'pim/entity-manager'], function (module, $, _, EntityManager) {
    return {
        getProductGroups: function (product) {
            var promises = _.map(product.groups, function (groupCode) {
                return EntityManager.getRepository('group').find(groupCode);
            });

            if (product.variant_group) {
                promises.push(EntityManager.getRepository('variantGroup').find(product.variant_group));
            }

            return $.when.apply($, promises).then(function () {
                return Array.prototype.slice.call(arguments);
            });
        }
    };
});

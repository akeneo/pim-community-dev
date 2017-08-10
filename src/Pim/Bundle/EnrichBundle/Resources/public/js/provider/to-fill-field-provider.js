'use strict';
/**
 * Attribute group selector extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/mediator',
        'pim/attribute-manager',
        'pim/fetcher-registry'
    ],
    function ($, _, mediator, attributeManager, fetcherRegistry) {
        return {
            fieldsPromise: null,

            /**
             * Get list of fields that need to be filled to complete the product
             *
             * @param {object} root
             * @param {object} product
             *
             * @return {promise}
             */
            getFields: function (root, product) {
                var filterPromises = [];
                root.trigger(
                    'pim_enrich:form:field:to-fill-filter',
                    {'filters': filterPromises}
                );

                if (null == this.fieldsPromise) {
                    this.fieldsPromise = $.when.apply($, filterPromises).then(function () {
                        return arguments;
                    }).then(function (filters) {
                        return attributeManager.getAttributes(product)
                            .then(function (attributeCodes) {
                                return fetcherRegistry.getFetcher('attribute').fetchByIdentifiers(attributeCodes);
                            })
                            .then(function (attributesToFilter) {
                                var filteredAttributes = _.reduce(filters, function (attributes, filter) {
                                    return filter(attributes);
                                }, attributesToFilter);

                                return _.map(filteredAttributes, function (attribute) {
                                    return attribute.code;
                                });
                            });
                    });
                }

                return this.fieldsPromise
            },

            clear: function () {
                this.fieldsPromise = null;
            }
        };
    }
);

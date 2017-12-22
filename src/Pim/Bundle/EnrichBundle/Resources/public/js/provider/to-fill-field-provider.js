'use strict';
/**
 * To fill field provider
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
    function (
        $,
        _,
        mediator,
        attributeManager,
        fetcherRegistry
    ) {
        return {
            fieldsPromise: null,

            /**
             * Returns the missing required attributes of the current scope and locale for the given product
             *
             * @param product Object
             * @param scope String
             * @param locale String
             *
             * @return Array
             */
            getMissingRequiredFields: function (product, scope, locale) {
                const scopeMissingAttributes =  _.findWhere(product.meta.required_missing_attributes, {channel: scope});
                if (undefined === scopeMissingAttributes) {
                    return [];
                }

                const localeMissingAttributes = scopeMissingAttributes.locales[locale];
                if (undefined === localeMissingAttributes) {
                    return [];
                }

                const missingAttributeCodes = localeMissingAttributes.missing.map(missing => missing.code);
                const levelAttributeCodes = Object.keys(product.values);

                return missingAttributeCodes.filter(missingAttribute => levelAttributeCodes.includes(missingAttribute));
            },

            /**
             * Get list of fields that need to be filled to complete the product
             *
             * @param {Object} root
             * @param {Object} values
             *
             * @return {Promise}
             */
            getFields: function (root, values) {

                if (null === this.fieldsPromise) {
                    let filterPromises = [];
                    root.trigger(
                        'pim_enrich:form:field:to-fill-filter',
                        {'filters': filterPromises}
                    );

                    this.fieldsPromise = $.when.apply($, filterPromises).then(function () {
                        return arguments;
                    }).then(function (filters) {
                        return fetcherRegistry.getFetcher('attribute').fetchByIdentifiers(Object.keys(values))
                            .then(function (attributesToFilter) {
                                const filteredAttributes = _.reduce(filters, function (attributes, filter) {
                                    return filter(attributes);
                                }, attributesToFilter);

                                return _.map(filteredAttributes, function (attribute) {
                                    return attribute.code;
                                });
                            });
                    });
                }

                return this.fieldsPromise;
            },

            /**
             * Clear the to fill field cache
             */
            clear: function () {
                this.fieldsPromise = null;
            }
        };
    }
);

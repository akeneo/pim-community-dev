'use strict';

/**
 * Family edit form add attribute select extension view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/product/add-select/attribute',
        'pim/fetcher-registry'
    ],
    function (
        $,
        _,
        AddAttributeSelect,
        FetcherRegistry
    ) {
        return AddAttributeSelect.extend({

            /**
             * {@inheritdoc}
             */
            fetchItems: function (searchParameters) {
                return this.getItemsToExclude()
                    .then(function (attributeCodes) {
                        searchParameters.options.excluded_identifiers = attributeCodes;

                        return FetcherRegistry.getFetcher(this.mainFetcher).search(searchParameters)
                            .then(function (attributes) {
                                const groupCodes = _.unique(_.pluck(attributes, 'group'));

                                return FetcherRegistry.getFetcher('attribute-group').fetchByIdentifiers(groupCodes)
                                    .then(function (attributeGroups) {
                                        return this.populateGroupProperties(attributes, attributeGroups);
                                    }.bind(this));
                            }.bind(this));
                    }.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            getItemsToExclude: function () {
                return $.Deferred().resolve(
                    this.getFormData().attributes.map((attribute) => {
                        return attribute.code;
                    })
                );
            },

            /**
             * {@inheritdoc}
             */
            addItems: function () {
                this.getRoot().trigger(this.addEvent, {codes: this.selection});
            },

            /**
             * {@inheritdoc}
             */
            getSelectSearchParameters: function () {
                return _.extend({}, AddAttributeSelect.prototype.getSelectSearchParameters.apply(this, arguments), {
                    rights: 0
                });
            }
        });
    }
);


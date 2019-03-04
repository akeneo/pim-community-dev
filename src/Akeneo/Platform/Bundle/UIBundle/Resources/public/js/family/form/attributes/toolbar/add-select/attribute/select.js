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
             * Fetches items from the backend.
             *
             * @param {Object} searchParameters
             *
             * @return {Promise}
             */
            fetchItems: function (searchParameters) {
                return this.getItemsToExclude()
                    .then(function (familyCode) {
                        searchParameters.options.exclude_identifiers_from_family = familyCode;

                        return FetcherRegistry.getFetcher(this.mainFetcher).search(searchParameters);
                    }.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            getItemsToExclude: function () {
                return $.Deferred().resolve(this.getFormData().code);
            },

            /**
             * {@inheritdoc}
             */
            addItems: function () {
                this.getRoot().trigger(this.addEvent, { codes: this.selection });
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


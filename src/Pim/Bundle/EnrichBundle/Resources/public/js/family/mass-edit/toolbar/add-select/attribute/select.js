'use strict';

/**
 * Family mass edit form add attribute select extension view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/fetcher-registry',
        'pim/family-edit-form/attributes/toolbar/add-select/attribute'
    ],
    function (
        $,
        _,
        FetcherRegistry,
        FamilyAddAttributeSelect
    ) {
        return FamilyAddAttributeSelect.extend({
            /**
             * {@inheritdoc}
             */
            getItemsToExclude: function () {
                return FetcherRegistry.getFetcher(this.mainFetcher)
                    .getIdentifierAttribute()
                    .then(function (identifier) {
                        var existingAttributes = _.pluck(
                            this.getFormData().attributes,
                            'code'
                        );

                        if (!_.contains(existingAttributes, identifier.code)) {
                            existingAttributes.push(identifier.code);
                        }

                        return existingAttributes;
                    }.bind(this));
            }
        });
    }
);


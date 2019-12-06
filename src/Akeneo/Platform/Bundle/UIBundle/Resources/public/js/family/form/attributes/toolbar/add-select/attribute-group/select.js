'use strict';

/**
 * Family add attribute group select extension view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/common/add-select',
        'pim/fetcher-registry'
    ],
    function (
        $,
        _,
        __,
        BaseAddSelect,
        FetcherRegistry
    ) {
        return BaseAddSelect.extend({
            className: 'AknButtonList-item add-attribute-group',

            /**
             * Returns a set of attribute groups that are not empty, and not already added to the family.
             *
             * @param {Object} loadedGroups
             */
            filterAllowedAttributeGroups(loadedGroups) {
                const allowedGroups = {}

                Object.entries(loadedGroups).forEach(([group, data]) => {
                    const familyAttributes = this.getRoot().getFormData().attributes.filter((attribute) => {
                        return attribute.group === group;
                    }).map(attribute => attribute.code);
                    const groupIsNotEmpty = data.attributes.length > 0;
                    const groupIsIncomplete = data.attributes.length !== familyAttributes.length;

                    if (groupIsNotEmpty && groupIsIncomplete) {
                        allowedGroups[group] = data;
                    }
                });

                return allowedGroups;
            },

            /**
             * Fetches filtered attribute groups for the select
             *
             * @param {Promise} searchParameters
             */
            fetchItems(searchParameters) {
                return FetcherRegistry.getFetcher(this.mainFetcher)
                    .search(searchParameters)
                    .then((loadedGroups) => this.filterAllowedAttributeGroups(loadedGroups))
            }
        });
    }
);


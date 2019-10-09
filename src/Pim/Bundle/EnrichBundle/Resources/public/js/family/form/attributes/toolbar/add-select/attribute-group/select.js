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

            getFilteredGroups(loadedGroups) {
                const familyData = this.getRoot().getFormData();
                const familyGroups = {};

                familyData.attributes.forEach(attribute => {
                    familyGroups[attribute.group] = familyGroups[attribute.group] || []
                    familyGroups[attribute.group].push(attribute.code);
                })

                const groupsToExclude = Object.entries(loadedGroups).filter(([group, data]) => {
                    const familyGroupAttributes = familyGroups[group];
                    const loadedGroupAttribute = data.attributes;
                    console.log('family', familyGroupAttributes)
                    console.log('loaded', loadedGroupAttribute);
                })
            },

            fetchItems(searchParameters) {
                return FetcherRegistry.getFetcher(this.mainFetcher)
                    .search(searchParameters)
                    .then((loadedGroups) => {
                        const filteredGroups = this.getFilteredGroups(loadedGroups)
                        return loadedGroups;
                    })
            },
        });
    }
);


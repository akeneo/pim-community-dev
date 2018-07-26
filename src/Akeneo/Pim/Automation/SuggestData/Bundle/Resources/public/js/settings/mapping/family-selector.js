'use strict';

/**
 * This module allow user to select a catalog family for suggest data updating.
 * When he selects a new family, it updates the main root model with it.
 *
 * TODO
 * - Add badge for enabled families
 * - Automatically select the first one
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
define(
    [
        'pim/form/common/fields/simple-select-async',
        'pim/fetcher-registry',
    ],
    function (
        BaseSelect,
        FetcherRegistry
    ) {
        return BaseSelect.extend({
            events: {
                'change input': function (event) {
                    FetcherRegistry.getFetcher('suggest_data_family_mapping')
                        .fetch(this.getFieldValue(event.target), {cached: false})
                        .then((family) => {
                            this.setData(family);
                            this.getRoot().render();
                            const stateExtension = this.getRoot().getExtension('state');
                            if (stateExtension) {
                                // Reinitialize the state
                                stateExtension.collectAndRender();
                            }
                        });
                }
            },
        })
    }
);

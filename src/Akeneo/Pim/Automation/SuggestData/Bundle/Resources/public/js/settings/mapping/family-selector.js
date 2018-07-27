'use strict';

/**
 * This module allow user to select a catalog family for suggest data updating.
 * When he selects a new family, it updates the main root model with it.
 *
 * TODO
 * - Add badge for enabled families
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
define(
    [
        'pim/form/common/fields/simple-select-async',
        'pim/fetcher-registry',
        'pim/router'
    ],
    function (
        BaseSelect,
        FetcherRegistry,
        Router
    ) {
        return BaseSelect.extend({
            events: {
                'change input': function (event) {
                    FetcherRegistry.getFetcher('suggest_data_family_mapping')
                        .fetch(this.getFieldValue(event.target), {cached: false})
                        .then((family) => {
                            const hasRedirected = Router.redirectToRoute('akeneo_suggest_data_family_mapping_edit', {
                                identifier: family.code
                            });
                            if (false === hasRedirected) {
                                this.render();
                            } else {
                                return hasRedirected;
                            }
                        });
                }
            },
        })
    }
);

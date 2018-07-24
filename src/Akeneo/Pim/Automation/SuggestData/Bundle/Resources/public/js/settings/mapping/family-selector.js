'use strict';

/**
 * TODO
 * - Add badge for enabled families
 * - Automatically select the first one
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
define(
    [
        'pim/form/common/fields/simple-select-async'
    ],
    function (
        BaseSelect
    ) {
        return BaseSelect.extend({
            events: {
                'change input': function (event) {
                    FetcherRegistry.getFetcher('suggest_data_family_mapping')
                        .fetch('camcorders')
                        .then((family) => {

                            this.getRoot().render();
                        });
                }
            },
        })
    }
);

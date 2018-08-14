'use strict';

/**
 * This module allow user to select a catalog family for suggest data updating.
 * When he selects a new family, it updates the main root model with it.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
define(
    [
        'underscore',
        'pim/form/common/fields/simple-select-async',
        'pim/fetcher-registry',
        'pim/router',
        'pimee/template/settings/mapping/family-line'
    ],
    function (
        _,
        BaseSelect,
        FetcherRegistry,
        Router,
        LineTemplate
    ) {
        return BaseSelect.extend({
            lineView: _.template(LineTemplate),
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

            /**
             * {@inheritdoc}
             */
            getSelect2Options() {
                const parent = BaseSelect.prototype.getSelect2Options.apply(this, arguments);
                parent.formatResult = this.onGetResult.bind(this);
                parent.dropdownCssClass = 'select2--withIcon ' + parent.dropdownCssClass;

                return parent;
            },

            /**
             * Formats and updates list of items
             *
             * @param {Object} item
             *
             * @return {Object}
             */
            onGetResult(item) {
                return this.lineView({item});
            },

            /**
             * {@inheritdoc}
             */
            convertBackendItem(item) {
                const result = BaseSelect.prototype.convertBackendItem.apply(this, arguments);
                result.enabled = item.enabled;

                return result;
            }
        })
    }
);

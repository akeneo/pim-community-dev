'use strict';

/**
 * Attributes simple select
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
define([
    'jquery',
    'underscore',
    'pim/form/common/fields/simple-select-async',
    'pim/i18n',
    'pim/user-context',
    'pim/fetcher-registry',
    'pimee/template/settings/mapping/attribute-line'
], function (
    $,
    _,
    BaseSimpleSelect,
    i18n,
    UserContext,
    FetcherRegistry,
    LineTemplate
    ) {
        return BaseSimpleSelect.extend({
            className: 'AknFieldContainer AknFieldContainer--withoutMargin',
            lineView: _.template(LineTemplate),
            attributeGroups: {},

            /**
             * {@inheritdoc}
             */
            configure() {
                return $.when(
                    BaseSimpleSelect.prototype.configure.apply(this, arguments),
                    FetcherRegistry
                        .getFetcher('attribute-group')
                        .fetchAll()
                        .then((attributeGroups) => {
                            this.attributeGroups = attributeGroups;
                        })
                );
            },

            /**
             * {@inheritdoc}
             */
            getSelect2Options() {
                const parent = BaseSimpleSelect.prototype.getSelect2Options.apply(this, arguments);
                parent.allowClear = true;
                parent.formatResult = this.onGetResult.bind(this);
                parent.dropdownCssClass = 'select2--annotedLabels ' + parent.dropdownCssClass;

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
                return {
                    id: item.code,
                    text: i18n.getLabel(item.labels, UserContext.get('catalogLocale'), item.code),
                    group: {
                        text: (
                            item.group ?
                                i18n.getLabel(
                                    this.attributeGroups[item.group].labels,
                                    UserContext.get('catalogLocale'),
                                    this.attributeGroups[item.group]
                                ) : ''
                        )
                    }
                };
            },

            /**
             * {@inheritdoc}
             *
             * Removes the useless catalogLocale field, and adds localizable, is_locale_specific and scopable filters.
             */
            select2Data(term, page) {
                return {
                    localizable: false,
                    is_locale_specific: false,
                    scopable: false,
                    search: term,
                    options: {
                        limit: this.resultsPerPage,
                        page: page
                    }
                };
            }
        });
    }
);

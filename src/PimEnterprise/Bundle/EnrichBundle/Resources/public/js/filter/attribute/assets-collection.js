'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'routing',
    'pim/filter/attribute/select',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'jquery.select2'
], function (
    $,
    _,
    __,
    Routing,
    SelectFilter,
    FetcherRegistry,
    UserContext,
    i18n
) {
    return SelectFilter.extend({
        /**
         * {@inheritdoc}
         */
        getSelect2Options: function (attribute) {
            var choiceUrl = this.getChoiceUrl(attribute);

            return {
                ajax: {
                    url: choiceUrl,
                    cache: true,
                    data: function (term) {
                        return {
                            search: term,
                            options: {
                                locale: UserContext.get('uiLocale')
                            }
                        };
                    },
                    results: function (data) {
                        return this.parseAssetResponse(data);
                    }.bind(this)
                },
                initSelection: function (element, callback) {
                    this.getChoices(attribute).then(function (response) {
                        response = this.parseAssetResponse(response);
                        var results = response.results;

                        var choices = _.map($(element).val().split(','), function (choice) {
                            return _.findWhere(results, {id: choice});
                        });
                        callback(choices);
                    }.bind(this));
                }.bind(this),
                multiple: true
            };
        },

        /**
         * {@inheritdoc}
         */
        getType: function () {
            return 'pim-filter-attribute-select-reference-data';
        },

        /**
         * Clean invalid values by removing possibly non-existent options coming from database.
         * This method returns a promise which, once resolved, should return the attribute.
         *
         * @param {string} attribute
         *
         * @returns {Promise}
         */
        cleanInvalidValues: function (attribute, currentValues) {
            return this.getChoices(attribute).then(function (response) {
                var possibleValues = _.pluck(response, 'code');
                currentValues      = undefined !== currentValues ? currentValues : [];

                return _.intersection(currentValues, possibleValues);
            }.bind(this));
        },
        /**
         * Parses the normalized asset containing all information to keep the information needed for the select2
         *
         * @param {array} assets
         */
        parseAssetResponse: function (assets) {
            return {
                results: _.map(assets, function (asset) {
                    return {
                        id: asset.code,
                        text: i18n.getLabel([], UserContext.get('uiLocale'), asset.code)
                    };
                }),
                more: 0 !== assets.length
            };
        }
    });
});

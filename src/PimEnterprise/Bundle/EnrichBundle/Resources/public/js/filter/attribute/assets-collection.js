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
    'text!pim/template/filter/attribute/select',
    'jquery.select2'
], function (
    $,
    _,
    __,
    Routing,
    SelectFilter,
    FetcherRegistry,
    UserContext,
    i18n,
    template
) {
    return SelectFilter.extend({
        /**
         * {@inheritdoc}
         */
        getSelect2Options: function (attribute) {
            return FetcherRegistry.getFetcher(this.config.fetcherCode).fetchAll()
                .then(function (config) {
                    return Routing.generate(this.config.url);
                }.bind(this))
                .then(function (choiceUrl) {
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
                            if (null === this.choicePromise) {
                                this.choicePromise = $.get(choiceUrl);
                            }

                            this.choicePromise.then(function (response) {
                                var response = this.parseAssetResponse(response);
                                var results = response.results;

                                var choices = _.map($(element).val().split(','), function (choice) {
                                    return _.findWhere(results, {id: choice});
                                });
                                callback(choices);
                            }.bind(this));
                        }.bind(this),
                        multiple: true
                    };
                }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        getType: function () {
            return 'pim-filter-attribute-select-reference-data';
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
                        text: '[' + asset.code + ']'
                    };
                }),
                more: 0 !== assets.length
            };
        }
    });
});

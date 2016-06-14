/* global console */
'use strict';

define([
        'underscore',
        'oro/translator',
        'pim/filter/filter',
        'text!pim/template/filter/simpleselect',
        'routing',
        'pim/user-context',
        'pim/initselect2',
        'pim/fetcher-registry',
        'jquery.select2'
    ], function (_, __, BaseFilter, template, Routing, UserContext, initSelect2, fetcherRegistry) {
    return BaseFilter.extend({
        template: _.template(template),
        events: {
            'change input': 'updateState',
            'click .remove': 'removeFilter'
        },
        initialize: function () {
            this.config = { //To remove
                operators: [
                    'IN',
                    'NOT IN',
                    'EMPTY',
                    'NOT EMPTY'
                ]
            };
        },
        render: function () {
            this.getChoiceUrl().then(function (choiceUrl) {
                this.$el.empty().append(this.template({
                    __: __,
                    field: this.getField(),
                    operator: this.getOperator(),
                    value: this.getValue(),
                    removable: this.isRemovable(),
                    operators: this.config.operators
                }));

                var options = {
                    multiple: true,
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
                            return data;
                        }
                    },
                    initSelection: function (element, callback) {
                        var id = $(element).val();
                        if ('' !== id) {
                            $.get(choiceUrl).then(function (response) {
                                var results = response.results;
                                var choices = _.map($(element).val().split(','), function (choice) {
                                    return _.findWhere(results, {id: choice});
                                });
                                callback(choices);
                            });
                        }
                    }.bind(this),
                    placeholder: ' ',
                    allowClear: true
                };

                this.$('.select2[name="filter-value"]').select2(options);

                this.delegateEvents();
            }.bind(this));

            return this;
        },

        /**
         * Get the URL to retrieve the choice list for this select field
         *
         * @returns {Promise}
         */
        getChoiceUrl: function () {
            return fetcherRegistry.getFetcher('attribute').fetch(this.getField()).then(function (attribute) {
                return Routing.generate(
                    'pim_ui_ajaxentity_list',
                    {
                        class: 'PimCatalogBundle:AttributeOption', //Should be passed as configuration
                        dataLocale: UserContext.get('uiLocale'),
                        collectionId: attribute.id,
                        options: {type: 'code'}
                    }
                )
            });
        },
        updateState: function () {
            this.setData({
                'field': this.getField(),
                'operator': this.$('input[name="filter-operator"]').val(),
                'value': this.$('input[name="filter-value"]').val().split(',')
            });
        }
    });
});

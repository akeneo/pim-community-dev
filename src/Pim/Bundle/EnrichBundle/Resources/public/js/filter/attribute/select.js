'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'routing',
    'pim/filter/attribute/attribute',
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
    BaseFilter,
    FetcherRegistry,
    UserContext,
    i18n,
    template
) {
    return BaseFilter.extend({
        shortname: 'select',
        template: _.template(template),
        choicePromise: null,
        events: {
            'change [name="filter-value"], [name="filter-operator"]': 'updateState'
        },

        /**
         * {@inheritdoc}
         */
        initialize: function (config) {
            this.config = config.config;

            return BaseFilter.prototype.initialize.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        isEmpty: function () {
            return !_.contains(['EMPTY', 'NOT EMPTY'], this.getOperator()) &&
                (undefined === this.getValue() || '' === this.getValue());
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function () {
            return this.template({
                __: __,
                value: this.getValue(),
                field: this.getField(),
                operator: this.getOperator(),
                editable: this.isEditable(),
                operators: this.config.operators
            });
        },

        /**
         * {@inheritdoc}
         */
        postRender: function (templateContext) {
            this.$('.operator').select2({
                minimumResultsForSearch: -1
            });

            if (!_.contains(['EMPTY', 'NOT EMPTY'], this.getOperator())) {
                this.$('.value').select2(templateContext.select2Options);
            }
        },

        /**
         * {@inheritdoc}
         */
        getTemplateContext: function () {
            var field = this.getField().replace(/\.code$/, '');

            return FetcherRegistry
                .getFetcher('attribute')
                .fetch(field)
                .then(function (attribute) {
                    return this.getSelect2Options(attribute).then(function (select2Options) {
                        return {
                            label: i18n.getLabel(attribute.labels, UserContext.get('uiLocale'), attribute.code),
                            select2Options: select2Options,
                            removable: this.isRemovable(),
                            editable: this.isEditable()
                        };
                    }.bind(this));
                }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        updateState: function () {
            var cleanedValues = [];
            var operator = this.$('[name="filter-operator"]').val();

            if (!_.contains(['EMPTY', 'NOT EMPTY'], operator)) {
                var value = this.$('[name="filter-value"]').val().split(/[\s,]+/);
                cleanedValues = _.reject(value, function (val) {
                    return '' === val;
                });
            }

            this.setData({
                field: this.getField(),
                operator: operator,
                value: cleanedValues
            });
        },

        /**
         * Return a promise which, once resolved, returns the choice options
         * reference data to populate the select2.
         *
         * @returns {Promise}
         */
        getSelect2Options: function (attribute) {
            return FetcherRegistry.getFetcher(this.config.fetcherCode).fetchAll()
                .then(function (config) {
                    var entityClass = null;

                    if (!_.isUndefined(this.config.entityClass)) {
                        entityClass = this.config.entityClass;
                    } else {
                        entityClass = config[attribute.reference_data_name].class;
                    }

                    return Routing.generate(
                        this.config.url,
                        {
                            'class': entityClass,
                            'dataLocale': UserContext.get('uiLocale'),
                            'collectionId': attribute.id,
                            'options': {'type': 'code'}
                        }
                    );
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
                                return data;
                            }
                        },
                        initSelection: function (element, callback) {
                            if (null === this.choicePromise) {
                                this.choicePromise = $.get(choiceUrl);
                            }

                            this.choicePromise.then(function (response) {
                                var results = response.results;
                                var choices = _.map($(element).val().split(','), function (choice) {
                                    return _.findWhere(results, {id: choice});
                                });
                                callback(choices);
                            });
                        }.bind(this),
                        multiple: true
                    };
                }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        getField: function () {
            var fieldName = BaseFilter.prototype.getField.apply(this, arguments);

            if (-1 === fieldName.indexOf('.code')) {
                fieldName += '.code';
            }

            return fieldName;
        }
    });
});

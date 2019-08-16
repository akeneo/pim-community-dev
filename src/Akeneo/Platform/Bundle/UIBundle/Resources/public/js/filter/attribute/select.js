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
    'pim/template/filter/attribute/select',
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
        initialize: function () {
            this.choicePromise = null;

            return BaseFilter.prototype.initialize.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        configure: function () {
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', function (data) {
                _.defaults(data, {field: this.getCode() + '.code'});
            }.bind(this));

            return BaseFilter.prototype.configure.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        isEmpty: function () {
            return !_.contains(['EMPTY', 'NOT EMPTY'], this.getOperator()) &&
                (undefined === this.getValue() || _.isEmpty(this.getValue()));
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function () {
            if (undefined === this.getOperator()) {
                this.setOperator(_.first(_.values(this.config.operators)));
            }

            return this.template({
                __: __,
                value: this.getValue(),
                field: this.getField(),
                operator: this.getOperator(),
                editable: this.isEditable(),
                operators: this.getLabelledOperatorChoices(this.shortname)
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
            return FetcherRegistry
                .getFetcher('attribute')
                .fetch(this.getCode())
                .then(function (attribute) {
                    return this.cleanInvalidValues(attribute, this.getValue()).then(function (cleanedValues) {
                        if (!_.isEqual(this.getValue(), cleanedValues)) {
                            this.setValue(cleanedValues, {silent: false});
                        }

                        return {
                            label: i18n.getLabel(
                                attribute.labels,
                                UserContext.get('uiLocale'),
                                attribute.code
                            ),
                            select2Options: this.getSelect2Options(attribute),
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

            this.render();
        },

        /**
         * Return the choice options or reference data to populate the select2.
         *
         * @returns {Object}
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
                        return data;
                    }
                },
                initSelection: function (element, callback) {
                    this.getChoices(attribute).then(function (response) {
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
         * Return the select choice promise which, once resolved, return all possible choices
         * for the given select attribute.
         *
         * @param {string} attribute
         *
         * @returns {Promise}
         */
        getChoices: function (attribute) {
            var choiceUrl = this.getChoiceUrl(attribute);

            if (null === this.choicePromise) {
                this.choicePromise = $.get(choiceUrl);
            }

            return this.choicePromise;
        },

        /**
         * Get the string Url to access all select choices related to the given attribute.
         *
         * @param {string} attribute
         *
         * @returns {string}
         */
        getChoiceUrl: function (attribute) {
            return Routing.generate(
                this.config.url,
                {
                    class: this.config.entityClass,
                    dataLocale: UserContext.get('uiLocale'),
                    collectionId: attribute.meta.id,
                    options: {type: 'code'},
                    referenceDataName: attribute.reference_data_name
                }
            );
        },

        /**
         * Clean invalid values by removing possibly non-existent options coming from database.
         * This method returns a promise which, once resolved, should return the attribute.
         *
         * @param {string} attribute
         * @param {array} currentValues
         *
         * @returns {Promise}
         */
        cleanInvalidValues: function (attribute, currentValues) {
            return this.getChoices(attribute).then(function (response) {
                var possibleValues = _.pluck(response.results, 'id');
                currentValues  = undefined !== currentValues ? currentValues : [];

                return _.intersection(currentValues, possibleValues);
            }.bind(this));
        }
    });
});

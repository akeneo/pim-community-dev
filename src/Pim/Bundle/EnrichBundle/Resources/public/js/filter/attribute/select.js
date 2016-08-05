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

            return FetcherRegistry.getFetcher('attribute').fetch(field)
                .then(this.cleanInvalidValues.bind(this))
                .then(function (attribute) {
                    return {
                        label: i18n.getLabel(attribute.labels, UserContext.get('uiLocale'), attribute.code),
                        select2Options: this.getSelect2Options(attribute),
                        removable: this.isRemovable(),
                        editable: this.isEditable()
                    };
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
                    this.getChoicePromise(attribute).then(function (response) {
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
         * @param attribute
         *
         * @returns {Promise}
         */
        getChoicePromise: function (attribute) {
            var choiceUrl = this.getChoiceUrl(attribute);

            if (null === this.choicePromise) {
                this.choicePromise = $.get(choiceUrl);
            }

            return this.choicePromise;
        },

        /**
         * Get the string Url to access all select choices related to the given attribute.
         *
         * @param attribute
         *
         * @returns {string}
         */
        getChoiceUrl: function (attribute) {
            return Routing.generate(
                this.config.url,
                {
                    class: this.config.entityClass,
                    dataLocale: UserContext.get('uiLocale'),
                    collectionId: attribute.id,
                    options: {type: 'code'},
                    referenceDataName: attribute.reference_data_name
                }
            );
        },

        /**
         * Clean invalid values by removing possibly non-existent options coming from database.
         * This method returns a promise which, once resolved, should return the attribute.
         *
         * @param attribute
         *
         * @returns {Promise}
         */
        cleanInvalidValues: function (attribute) {
            return this.getChoicePromise(attribute).then(function (response) {
                var results = response.results;
                var initialValue = this.getValue();
                var idResults = _.pluck(results, 'id');

                // Update field value if some options are not available anymore
                if (!_.isEmpty(_.difference(initialValue, idResults))) {
                    this.setValue(_.intersection(initialValue, idResults), {silent: false});
                }

                return attribute;
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

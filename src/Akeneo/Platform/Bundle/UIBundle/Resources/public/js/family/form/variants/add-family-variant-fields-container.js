'use strict';
/**
 * Fields container of the create family variant form.
 * This module contains all the fields and the logic to update the family variant model.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/initselect2',
        'pim/form',
        'pim/template/family-variant/add-variant-form-fields-container'
    ],
    function(
        $,
        _,
        __,
        UserContext,
        FetcherRegistry,
        initSelect2,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            queryTimer: null,
            validationErrors: [],

            events: {
                'change select, input[name="code"], input[name="label"]': function() {
                    this.updateModel();
                    this.render();
                }
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:pre_save',
                    this.resetValidationErrors.bind(this)
                );

                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:validation_error',
                    this.setValidationErrors.bind(this)
                );

                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:validation_error',
                    this.render.bind(this)
                );

                this.setData(this.getNewFamilyVariant());

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                const catalogLocale = UserContext.get('catalogLocale');
                const familyVariant = _.defaults(this.getFormData(), this.getNewFamilyVariant());
                const label = this.getEntityLabel(familyVariant, catalogLocale);
                const axes1 = familyVariant.variant_attribute_sets[0].axes.join(',');
                const axes2 = familyVariant.variant_attribute_sets[1]
                    ? familyVariant.variant_attribute_sets[1].axes.join(',')
                    : ''
                ;

                const globalErrors = this.validationErrors.filter((error) => {
                    return true === error.global || undefined === error.path || 'variant_attribute_sets' === error.path;
                });

                const fieldErrors = _.groupBy(this.validationErrors, 'path');

                this.$el.html(
                    this.template({
                        __: __,
                        familyVariant: familyVariant,
                        catalogLocale: catalogLocale,
                        axes1: axes1,
                        axes2: axes2,
                        label: label,
                        globalErrors: globalErrors,
                        fieldErrors: fieldErrors
                    })
                );

                this.initializeSelectWidgets();
            },

            /**
             * Initialize the select2 widgets for axis fields. These select2 fetch attributes available as axis
             * for the family variant.
             */
            initializeSelectWidgets() {
                const $selects = this.$('input[type="hidden"]');

                _.each($selects, (select) => {
                    const $select = $(select);
                    const options = {
                        dropdownCssClass: '',
                        closeOnSelect: false,
                        multiple: true,
                        query: this.queryAvailableAxes.bind(this),
                        initSelection: this.initSelection.bind(this)
                    };

                    const dropdown = initSelect2.init($select, options);
                    dropdown.on('change', () => {
                        this.updateModel();
                    });
                });
            },

            /**
             * This methods fetches available axis attributes for the family this family variant is based on.
             *
             * The options parameter is the one needed by select2, it contains the 'term' for the search, and the
             * 'callback' method for the results.
             *
             * @param {Object} options
             */
            queryAvailableAxes(options) {
                const familyCode = this.getFormData().family;

                clearTimeout(this.queryTimer);
                this.queryTimer = setTimeout(() => {
                    FetcherRegistry
                        .getFetcher('family')
                        .fetchAvailableAxes(familyCode)
                        .then((attributes) => {
                            const catalogLocale = UserContext.get('catalogLocale');
                            const attributeResults = this.searchOnResults(options.term, attributes);
                            const entities = _.map(attributeResults, (attribute) => {
                                const label = this.getEntityLabel(attribute, catalogLocale);

                                return {
                                    id: attribute.code,
                                    text: label
                                };
                            });

                            const sortedEntities = _.sortBy(entities, 'text');

                            options.callback({
                                results: sortedEntities
                            });
                        });
                }, 400);
            },

            /**
             * Return all attributes that have a label that match the given term.
             *
             * @param {string} term
             * @param {array} attributes
             *
             * @returns {array}
             */
            searchOnResults(term, attributes) {
                const catalogLocale = UserContext.get('catalogLocale');
                term = term.toLowerCase();

                return attributes.filter((entity) => {
                    const label = this.getEntityLabel(entity, catalogLocale).toLowerCase();

                    return -1 !== label.search(term);
                });
            },

            /**
             * Initialize selection of select2 widgets. We need to fetch labels of selected attributes, because we
             * only have their code.
             *
             * @param {Object} element
             * @param {Function} callback
             */
            initSelection : function (element, callback) {
                const attributeCodes = element.val().split(',');
                const catalogLocale = UserContext.get('catalogLocale');

                const fetchAttributesPromises = _.map(attributeCodes, (attributeCode) => {
                    return FetcherRegistry.getFetcher('attribute').fetch(attributeCode).promise();
                });

                $.when.apply($, fetchAttributesPromises)
                    .then(function () {
                        const data = _.map(arguments, (attribute) => {
                            const label = this.getEntityLabel(attribute, catalogLocale);

                            return {
                                id: attribute.code,
                                text: label
                            };
                        });

                        callback(data);
                    }.bind(this));
            },

            /**
             * Update the family variant model
             */
            updateModel() {
                const catalogLocale = UserContext.get('catalogLocale');
                const axisLevelOne = _.compact(this.$('input[name="axes1"]').val().split(','));
                const numberOfLevels = parseInt(this.$('select[name="numberOfLevels"]').val());

                let attributeSets = [];
                attributeSets.push({level: 1, axes: axisLevelOne, attributes: []});
                if (numberOfLevels > 1) {
                    const axisLevelTwo = _.compact(this.$('input[name="axes2"]').val().split(','));
                    attributeSets.push({level: 2, axes: axisLevelTwo, attributes: []});
                }

                let labels = {};
                labels[catalogLocale] = this.$('input[name="label"]').val();

                this.setData({
                    code: this.$('input[name="code"]').val(),
                    labels: labels,
                    variant_attribute_sets: attributeSets
                });
            },

            /**
             * Get a new empty family variant object.
             *
             * @returns {Object}
             */
            getNewFamilyVariant() {
                const catalogLocale = UserContext.get('catalogLocale');

                return {
                    code: '',
                    labels: {
                        [catalogLocale]: ''
                    },
                    variant_attribute_sets: [
                        {level: 1, axes: [], attributes: []}
                    ]
                };
            },

            /**
             * Reset the validation errors of the form.
             */
            resetValidationErrors() {
                this.validationErrors = [];
            },

            /**
             * Set the validation errors of the form.
             */
            setValidationErrors(errors) {
                this.validationErrors = errors;
            },

            /**
             * Return the label/code of a serialized entity.
             *
             * @param {string} entity
             * @param {string} locale
             * @returns {string}
             */
            getEntityLabel(entity, locale) {
                if (0 === entity.labels.length || entity.labels[locale] === undefined) {
                    return '[' + entity.code + ']';
                }

                return entity.labels[locale];
            }
        });
    }
);

'use strict';
/**
 * Creation form of a family variant.
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
        'pim/i18n',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/initselect2',
        'pim/form',
        'pim/template/family/tab/variants/add-variant-form',
        'bootstrap-modal'
    ],
    function(
        $,
        _,
        __,
        i18n,
        UserContext,
        FetcherRegistry,
        initSelect2,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            queryTimer: null,
            dropdowns: {},
            family: {},

            events: {
                'change select[name="numberOfLevels"]': 'displayAxisFields'
            },

            /**
             * {@inheritdoc}
             */
            render() {
                const family = this.family;
                const catalogLocal = UserContext.get('catalogLocale');

                this.$el.html(
                    this.template({
                        __: __,
                        familyName: i18n.getLabel(family.labels, catalogLocal, family.code),
                    })
                );

                this.displayAxisFields();
                this.initializeSelectWidgets();
            },

            /**
             * Display the correct number of axis fields, depending on the number of levels the family variant has.
             */
            displayAxisFields() {
                const numberOfLevels = parseInt(this.$('select[name="numberOfLevels"]').val());

                if (numberOfLevels > 1) {
                    this.$('.axis_level_2').show();
                } else {
                    this.$('.axis_level_2').hide();
                }
            },

            /**
             * Initialize the select2 widgets for axis fields. These select2 fetch attributes available as axis
             * for the family variant.
             */
            initializeSelectWidgets() {
                const $selects = this.$('input[type="hidden"]');

                _.each($selects, (select, index) => {
                    const $select = $(select);
                    const options = {
                        dropdownCssClass: '',
                        closeOnSelect: false,
                        multiple: true,
                        query: (options) => {
                            this.queryAvailableAxes(options)
                        }
                    };

                    this.dropdowns[index] = initSelect2.init($select, options);
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
                const family = this.family;

                clearTimeout(this.queryTimer);
                this.queryTimer = setTimeout(() => {
                    FetcherRegistry
                        .getFetcher('family')
                        .fetchAvailableAxes(family.code)
                        .then((attributes) => {
                            const catalogLocale = UserContext.get('catalogLocale');
                            const attributeResults = this.searchOnResults(options.term, attributes);
                            const entities = _.map(attributeResults, (attribute) => {
                                return {
                                    id: attribute.code,
                                    text: attribute.labels[catalogLocale],
                                }
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
                    const label = entity.labels[catalogLocale].toLowerCase();

                    return -1 !== label.search(term);
                });
            },

            /**
             * Set the family this new family variant will be based on.
             *
             * @param {Object} family
             */
            setFamily(family) {
                this.family = family;
            },

            /**
             * Saves the family variant backbone model of this form, filled with all the fields.
             */
            saveModel() {
                const nbOfLevels = parseInt(this.$('select[name="numberOfLevels"]').val());
                const catalogLocale = UserContext.get('catalogLocale');
                const axisLevelOne = _.compact(this.dropdowns[0].val().split(','));

                let attributeSets = [];
                attributeSets.push({level: 1, axes: axisLevelOne, attributes: []});

                if (nbOfLevels > 1) {
                    const axisLevelTwo = _.compact(this.dropdowns[1].val().split(','));
                    attributeSets.push({level: 2, axes: axisLevelTwo, attributes: []});
                }


                let labels = {};
                labels[catalogLocale] = this.$('input[name="label"]').val();

                this.model.set({
                    family: this.family.code,
                    code: this.$('input[name="code"]').val(),
                    labels: labels,
                    variant_attribute_sets: attributeSets
                });
            }
        });
    }
);

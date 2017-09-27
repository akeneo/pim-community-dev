'use strict';

/**
 * Extension to display the variant navigation to browse variant product structure (parents and children)
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'jquery',
        'underscore',
        'oro/translator',
        'pim/router',
        'pim/i18n',
        'pim/user-context',
        'pim/form',
        'pim/initselect2',
        'pim/fetcher-registry',
        'pim/media-url-generator',
        'pim/template/product/form/variant-navigation/navigation',
        'pim/template/product/form/variant-navigation/product-item',
        'pim/template/product/form/variant-navigation/product-model-item'
    ],
    function (
        $,
        _,
        __,
        router,
        i18n,
        UserContext,
        BaseForm,
        initSelect2,
        FetcherRegistry,
        MediaUrlGenerator,
        template,
        templateProduct,
        templateProductModel
    ) {
        return BaseForm.extend({
            template: _.template(template),
            templateProduct: _.template(templateProduct),
            templateProductModel: _.template(templateProductModel),
            dropdowns: {},
            queryTimer: null,
            events: {
                'click [data-action="navigateToLevel"]': 'navigateToLevel'
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                const entity = this.getFormData();
                const catalogLocale = UserContext.get('catalogLocale');

                if ('product' === entity.meta.model_type && null === entity.parent) {
                    this.$el.html('');

                    return;
                }

                this.$el.html(
                    this.template({
                        commonLabel: __('pim_enrich.entity.product.common'),
                        currentLocale: catalogLocale,
                        entity: entity,
                        navigation: entity.meta.variant_navigation
                    })
                );

                this.initializeSelectWidgets();
            },

            /**
             * Initialize the Select2 component and format elements.
             */
            initializeSelectWidgets: function () {
                const entity = this.getFormData();
                const $selects = this.$('.select-field');

                _.each($selects, (select, index) => {
                    const $select = $(select);
                    const options = {
                        dropdownCssClass: 'variant-navigation',
                        closeOnSelect: false,

                        /**
                         * Format result (product or product model variations) method of select2.
                         * This way we can display entities and their info beside them (completeness, image..).
                         */
                        formatResult: (item, $container) => {
                            const catalogLocale = UserContext.get('catalogLocale');
                            const filePath = (null !== item.image) ? item.image.filePath : null;
                            const entity = {
                                label: item.axes_values_labels[catalogLocale],
                                image: MediaUrlGenerator.getMediaShowUrl(filePath, 'thumbnail_small'),
                                completeness: this.parseCompleteness(item)
                            };

                            const html = ('product' === item.model_type)
                                ? this.templateProduct({ entity: entity })
                                : this.templateProductModel({ entity: entity })
                            ;

                            $container.append(html);
                        },

                        query: (options) => {
                            this.queryChildrenEntities(
                                options,
                                entity.meta.variant_navigation[index].selected.id
                            )
                        }
                    };

                    const dropdown = initSelect2.init($select, options);
                    dropdown.on('select2-selecting', (event) => {
                        this.redirectToEntity(event.object)
                    });

                    this.dropdowns[index] = dropdown;
                });
            },

            /**
             * Return a string of the completeness for the given entity to be displayed.
             *
             * @param {Object} entity
             *
             * @returns {string}
             */
            parseCompleteness: function (entity) {
                const catalogLocale = UserContext.get('catalogLocale');
                const catalogScope = UserContext.get('catalogScope');

                if ('product' === entity.model_type) {
                    const channelCompletenesses = _.findWhere(entity.completeness, {channel: catalogScope});
                    const localeCompleteness = channelCompletenesses.locales[catalogLocale].completeness;

                    return {
                        ratio: localeCompleteness.ratio
                    };
                } else {
                    // TODO: replace this placeholder by the real values, with PIM-6560
                    return {
                        ratio: 20,
                        display: '- / -'
                    };
                }
            },

            /**
             * Return all entities that have a label (axes values) that match the given term.
             *
             * @param {string} term
             * @param {array} entities
             *
             * @returns {array}
             */
            searchOnResults: function (term, entities) {
                const catalogLocale = UserContext.get('catalogLocale');
                term = term.toLowerCase();

                return entities.filter((entity) => {
                    const label = entity.axes_values_labels[catalogLocale].toLowerCase();

                    return -1 !== label.search(term);
                });
            },

            /**
             * Fetch all children of the given parentId and calls the callback of Select2 to
             * display them in the dropdown.
             *
             * @param {Object} options
             * @param {int} parentId
             */
            queryChildrenEntities: function (options, parentId) {
                clearTimeout(this.queryTimer);
                this.queryTimer = setTimeout(() => {
                    FetcherRegistry
                        .getFetcher('product-model')
                        .fetchChildren(parentId)
                        .then((children) => {
                            const childrenResults = this.searchOnResults(options.term, children);
                            const sortedChildrenResults = _.sortBy(childrenResults, 'order_string');

                            options.callback({
                                results: sortedChildrenResults
                            });
                        });
                }, 400);
            },

            /**
             * Redirect to the entity of the given level
             */
            navigateToLevel: function (event) {
                const entity = this.getFormData();
                const level = $(event.target).data('level');

                this.redirectToEntity(entity.meta.variant_navigation[level].selected);
            },

            /**
             * Redirect the user to the given entity edit page
             *
             * @param entity
             */
            redirectToEntity: function (entity) {
                const params = {
                    id: entity.id
                };

                const route = ('product_model' === entity.model_type)
                    ? 'pim_enrich_product_model_edit'
                    : 'pim_enrich_product_edit'
                ;

                router.redirectToRoute(route, params);
            }
        });
    }
);

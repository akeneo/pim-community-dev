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
            className: 'AknVariantNavigation',
            template: _.template(template),
            templateProduct: _.template(templateProduct),
            templateProductModel: _.template(templateProductModel),
            levelOneDropdown: null,
            levelTwoDropdown: null,
            queryTimer: null,
            events: {
                'click [data-action="navigateToRoot"]': 'redirectToRoot',
                'click [data-action="navigateToModel"]': 'redirectToModel'
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                UserContext.off('change:catalogLocale change:catalogScope', this.render);
                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                const entity = this.getFormData();
                const catalogLocale = UserContext.get('catalogLocale');
                const nbOfLevels = _.isEmpty(entity.meta.variant_navigation.level_two.axes) ? 1 : 2;
                let currentLevel = 0;

                if (null !== entity.parent) {
                    currentLevel = (entity.parent === entity.meta.variant_navigation.root.identifier) ? 1 : 2;
                }

                this.$el.html(
                    this.template({
                        commonLabel: __('pim_enrich.entity.product.common').toUpperCase(),
                        currentLocale: catalogLocale,
                        navigation: entity.meta.variant_navigation,
                        currentLevel: currentLevel,
                        nbOfLevels: nbOfLevels
                    })
                );

                this.initializeSelectWidgets();
            },

            /**
             * Initialize the Select2 component and format elements.
             */
            initializeSelectWidgets: function () {
                const entity = this.getFormData();

                const $levelOneSelect = this.$('.select-level-one');
                const $levelTwoSelect = this.$('.select-level-two');

                const commonOptions = {
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
                };

                const optionsLevelOne = $.extend(true, {
                    query: (options) => {
                        this.queryChildrenEntities(
                            options,
                            entity.meta.variant_navigation.root.id
                        );
                    }
                }, commonOptions);

                this.levelOneDropdown = initSelect2.init($levelOneSelect, optionsLevelOne);
                this.levelOneDropdown.on('select2-selecting', function (event) {
                    this.redirectToEntity(event.object)
                }.bind(this));

                if (null !== entity.meta.variant_navigation.level_one.selected) {
                    const optionsLevelTwo = $.extend(true, {
                        query: (options) => {
                            this.queryChildrenEntities(
                                options,
                                entity.meta.variant_navigation.level_one.selected.id
                            )
                        }
                    }, commonOptions);

                    this.levelTwoDropdown = initSelect2.init($levelTwoSelect, optionsLevelTwo);
                    this.levelTwoDropdown.on('select2-selecting', function (event) {
                        this.redirectToEntity(event.object)
                    }.bind(this));
                }
            },

            /**
             * Take incoming data and format them to have all required parameters
             * to be used by the select2 module.
             *
             * @param {array} data
             *
             * @return {array}
             */
            toSelect2Format: function (data) {
                const catalogLocale = UserContext.get('catalogLocale');

                return _.map(data, function (entity) {
                    entity.text = entity.axes_values_labels[catalogLocale];

                    return entity;
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
                        display: '1 / 5'
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

                return _.filter(entities, (entity) => {
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

                            options.callback({
                                results: childrenResults
                            });
                        });
                }, 400);
            },

            /**
             * Redirect to the root product model
             */
            redirectToRoot: function () {
                const entity = this.getFormData();
                this.redirectToEntity(entity.meta.variant_navigation.root)
            },

            /**
             * Redirect to the sub product model
             */
            redirectToModel: function () {
                const entity = this.getFormData();
                this.redirectToEntity(entity.meta.variant_navigation.level_one.selected)
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
            },
        });
    }
);

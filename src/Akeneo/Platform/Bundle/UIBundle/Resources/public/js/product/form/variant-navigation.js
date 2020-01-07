'use strict';

/**
 * Extension to display the variant navigation to browse variant product structure (parents and children)
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
        'pim/router',
        'pim/i18n',
        'pim/user-context',
        'pim/form',
        'pim/security-context',
        'pim/initselect2',
        'pim/fetcher-registry',
        'pim/media-url-generator',
        'oro/messenger',
        'pim/form-modal',
        'pim/template/product/form/variant-navigation/navigation',
        'pim/template/product/form/variant-navigation/product-item',
        'pim/template/product/form/variant-navigation/product-model-item',
        'pim/template/product/form/variant-navigation/add-child-button'
    ],
    function (
        $,
        _,
        __,
        router,
        i18n,
        UserContext,
        BaseForm,
        SecurityContext,
        initSelect2,
        FetcherRegistry,
        MediaUrlGenerator,
        messenger,
        FormModal,
        template,
        templateProduct,
        templateProductModel,
        templateAddChild
    ) {
        return BaseForm.extend({
            template: _.template(template),
            templateProduct: _.template(templateProduct),
            templateProductModel: _.template(templateProductModel),
            templateAddChild: _.template(templateAddChild),
            dropdowns: [],
            formModal: null,
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
            shutdown: function () {
                this.dropdowns.forEach(dropdown => {
                    dropdown.close();
                });

                if (this.formModal) {
                    this.formModal.close();
                    this.formModal.$el.remove();
                }

                BaseForm.prototype.shutdown.apply(this, arguments);
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
                        commonLabel: __('pim_enrich.entity.product.module.variant_navigation.common'),
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
                                ? this.templateProduct({entity: entity, getClass: this.getCompletenessBadgeClass})
                                : this.templateProductModel({entity: entity, getClass: this.getCompletenessBadgeClass})
                            ;

                            $container.append(html);
                        },

                        query: (options) => {
                            this.queryChildrenEntities(
                                options,
                                entity.meta.variant_navigation[index].selected.id
                            );
                        }
                    };

                    const dropDown = initSelect2.init($select, options);

                    dropDown
                        .on('select2-selecting', (event) => {
                            this.redirectToEntity(event.object);
                        })
                        .on('select2-open', () => {
                            this.addSelect2Footer(dropDown)
                        });

                    this.dropdowns.push(dropDown.data('select2'));
                });
            },

            /**
             * Adds the footer containing the creation button to the select2 dropdown.
             *
             * @param {Element} dropDown
             */
            addSelect2Footer: function(dropDown) {
                $('#select2-drop .select2-drop-footer').remove();

                const targetLevel = dropDown[0].dataset.level;
                this.getEntityParentCode(targetLevel)
                    .then((parentCode) => {
                        this.isVariantProduct(parentCode)
                            .then(async (isVariantProduct) => {
                                if (!await this.isCreationGranted(isVariantProduct)) {
                                    return;
                                }

                                const footer = this.templateAddChild({
                                    label: __('pim_enrich.entity.product_model.module.variant_axis.create')
                                });

                                $('#select2-drop')
                                    .append(footer)
                                    .find('.select2-drop-footer').on('click', '.add-child', () => {
                                        dropDown.select2('close');
                                        this.openModal(parentCode);
                                    });
                            });
                    })
            },

            /**
             * Tests the creation ACL depending on the entity type the user wants to create.
             *
             * @param {boolean} isVariantProduct
             *
             * @returns {Promise<boolean>}
             */
            isCreationGranted: async function(isVariantProduct) {
                return (isVariantProduct && SecurityContext.isGranted('pim_enrich_product_create'))
                    || (!isVariantProduct && SecurityContext.isGranted('pim_enrich_product_model_create'));
            },

            /**
             * Get the parent code for the new product model / variant product child.
             *
             * @param {Number} targetLevel
             *
             * @return {Promise}
             */
            getEntityParentCode: function (targetLevel) {
                const entity = this.getFormData();
                const entityLevel = entity.meta.level;

                if (targetLevel < entityLevel) {
                    return FetcherRegistry
                        .getFetcher('product-model-by-code')
                        .fetch(entity.parent)
                        .then((parent) => {
                            return parent.parent;
                        })
                    ;
                }

                if (targetLevel > entityLevel) {
                    return $.Deferred().resolve(entity.code).promise();
                }

                return $.Deferred().resolve(entity.parent).promise();
            },

            /**
             * Opens the modal containing the form to create a new family variant.
             *
             * @param {String} parentCode
             */
            openModal: function (parentCode) {
                const modalParameters = {
                    className: 'modal modal--fullPage add-product-model-child',
                    content: '',
                    cancelText: __('pim_common.cancel'),
                    okText: __('pim_common.confirm'),
                    okCloses: false
                };

                this.isVariantProduct(parentCode)
                    .then((isVariantProduct) => {
                        const initialModalState = {
                            parent: parentCode,
                            values: {}
                        };

                        if (isVariantProduct) {
                            initialModalState.family = this.getFormData().family;
                        } else {
                            initialModalState.family_variant = this.getFormData().family_variant;
                        }

                        const formModal = new FormModal(
                            'pim-product-model-add-child-form',
                            this.submitForm.bind(this, isVariantProduct),
                            modalParameters,
                            initialModalState
                        );

                        formModal.open();

                        this.formModal = formModal;
                    });
            },

            /**
             * Action made when user submit the modal.
             *
             * @param {boolean} isVariantProduct
             * @param {Object} formModal
             */
            submitForm: function (isVariantProduct, formModal) {
                const message = isVariantProduct
                    ? __('pim_enrich.entity.product_model.flash.create.variant_product_added')
                    : __('pim_enrich.entity.product_model.flash.create.product_model_added');

                const route = isVariantProduct
                    ? 'pim_enrich_product_rest_create'
                    : 'pim_enrich_product_model_rest_create';

                return formModal
                    .saveProductModelChild(route)
                    .done((entity) => {
                        this.redirectToEntity(entity.meta);
                        messenger.notify('success', message);
                    });
            },

            /**
             * Returns whether the new entity will be a variant product or a product model
             * using the parent code.
             *
             * @param {string} parentCode
             *
             * @returns {Promise}
             */
            isVariantProduct: function(parentCode) {
                return FetcherRegistry
                    .getFetcher('product-model-by-code')
                    .fetch(parentCode)
                    .then((parent) => {
                        return FetcherRegistry
                            .getFetcher('family-variant')
                            .fetch(parent.family_variant)
                            .then((familyVariant) => {
                                const currentLevel = parent.meta.level + 1;

                                return currentLevel === familyVariant.variant_attribute_sets.length;
                            })
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
                    if (channelCompletenesses === undefined) {
                        return {
                            ratio: 0
                        };
                    }
                    const localeCompleteness = channelCompletenesses.locales[catalogLocale].completeness;

                    return {
                        ratio: localeCompleteness.ratio
                    };
                } else {
                    const completenesses = entity.completeness.completenesses;
                    const totalProducts  = entity.completeness.total;
                    let completeProducts = 0;

                    if (_.has(completenesses, catalogScope) &&
                        _.has(completenesses[catalogScope], catalogLocale)
                    ) {
                        completeProducts = completenesses[catalogScope][catalogLocale];
                    }

                    return {
                        ratio: (completeProducts > 0) ? Math.floor(totalProducts / completeProducts * 100) : 0,
                        display: completeProducts + ' / ' + totalProducts
                    };
                }
            },

            /**
             * Get the CSS class for the completeness badge of the template, depending on the given ratio.
             *
             * @param {int} ratio
             *
             * @returns {string}
             */
            getCompletenessBadgeClass: function (ratio) {
                if (0 === ratio) {
                    return 'empty';
                }

                if (100 === ratio) {
                    return 'complete';
                }

                return 'incomplete';
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
                            const sortedChildrenResults = childrenResults.sort((item1, item2) => {
                                for (let i = 0; i < item1.order.length; i++) {
                                    if (item1.order[i] > item2.order[i]) {
                                        return 1;
                                    } else if (item1.order[i] < item2.order[i]) {
                                        return -1;
                                    }
                                }

                                return 0;
                            });

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
             * @param {Object} entity
             */
            redirectToEntity: function (entity) {
                if (!entity) {
                    return;
                }

                const params = {id: entity.id};
                const route = ('product_model' === entity.model_type)
                    ? 'pim_enrich_product_model_edit'
                    : 'pim_enrich_product_edit'
                ;

                router.redirectToRoute(route, params);
            }
        });
    }
);

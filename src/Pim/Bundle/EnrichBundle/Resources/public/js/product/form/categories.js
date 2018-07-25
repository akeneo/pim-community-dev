'use strict';
/**
 * Category tab extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/form',
        'pim/template/product/tab/categories',
        'pim/template/product/tab/catalog-switcher',
        'pim/template/product/tab/jstree-locked-item',
        'pim/user-context',
        'routing',
        'pim/tree/associate',
        'oro/mediator'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        formTemplate,
        switcherTemplate,
        lockedTemplate,
        UserContext,
        Routing,
        TreeAssociate,
        mediator
    ) {
        return BaseForm.extend({
            template: _.template(formTemplate),
            switcherTemplate: _.template(switcherTemplate),
            lockedTemplate: _.template(lockedTemplate),
            className: 'tab-pane active',
            id: 'product-categories',
            treeLinkSelector: 'tree-link-',
            treeHasItemClass: 'tree-has-item',
            events: {
                'click .nav-tabs li': 'changeTree',
                'change #hidden-tree-input': 'updateModel'
            },
            treeAssociate: null,
            cache: {},
            trees: [],
            onLoadedEvent: null,

            /**
             * Associates the tree code to the number of selected categories
             * Example: { master: 1, sales: 12 }
             */
            categoriesCount: {},

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.state = new Backbone.Model();

                this.state.set('selectedCategories', []);

                if (undefined !== config) {
                    this.config = config.config;
                }

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: (undefined === this.config.tabCode) ? this.code : this.config.tabCode,
                    isVisible: this.isVisible.bind(this),
                    label: __('pim_enrich.entity.category.plural_label')
                });

                this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', function (localeEvent) {
                    if ('base_product' === localeEvent.context) {
                        this.render();
                    }
                }.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.loadTrees().done(function (trees) {
                    this.trees = trees;

                    if (undefined === this.state.toJSON().currentTree) {
                        this.state.set('currentTree', _.first(this.trees).code);
                        this.state.set('currentTreeId', _.first(this.trees).id);
                    }

                    this.$el.html(
                        this.template({
                            product: this.getFormData(),
                            locale: UserContext.get('catalogLocale'),
                            state: this.state.toJSON(),
                            trees: this.trees
                        })
                    );

                    this.treeAssociate = new TreeAssociate('#trees', '#hidden-tree-input', {
                        list_categories: this.config.itemCategoryListRoute,
                        children:        'pim_enrich_categorytree_children'
                    });

                    this.delegateEvents();

                    this.onLoadedEvent = this.lockCategories.bind(this);
                    mediator.on('jstree:loaded', this.onLoadedEvent);

                    this.initCategoryCount();
                    this.renderCategorySwitcher();
                }.bind(this));

                return this;
            },

            /**
             * {@inheritdoc}
             */
            shutdown: function () {
                mediator.off('jstree:loaded', this.onLoadedEvent);

                BaseForm.prototype.shutdown.apply(this, arguments);
            },

            /**
             * Locks a set of categories
             */
            lockCategories: function() {
                const lockedCategoryIds = this.getFormData().meta.ascendant_category_ids;
                lockedCategoryIds.forEach((categoryId) => {
                    const node = $('#node_' + categoryId);
                    node.find('> a').replaceWith(this.lockedTemplate({
                        label: node.find('> a').text().trim()
                    }));
                });
            },

            /**
             * Renders the category switcher in the main template
             */
            renderCategorySwitcher: function () {
                this.$el.find('.catalog-switcher:first').html(this.switcherTemplate({
                    state: this.state.toJSON(),
                    trees: this.trees,
                    categoriesCount: this.categoriesCount,
                    treeLinkSelector: this.treeLinkSelector,
                    currentCategory: _.result(_.findWhere(
                        this.trees,
                        {code: this.state.toJSON().currentTree}),
                        'label'
                    )
                }));
            },

            /**
             * Load category trees
             *
             * @returns {promise}
             */
            loadTrees: function () {
                return $.getJSON(
                    Routing.generate(this.config.itemCategoryTreeRoute, { id: this.getFormData().meta.id })
                ).then(function (data) {
                    _.each(data.categories, function (category) {
                        this.cache[category.id] = category;
                    }.bind(this));

                    if (_.isEmpty(this.state.get('selectedCategories'))) {
                        this.state.set('selectedCategories', _.pluck(data.categories, 'id'));
                    }

                    return data.trees;
                }.bind(this));
            },

            /**
             * Displays the current tree when the user choose another one
             *
             * @param {Event} event
             */
            changeTree: function (event) {
                this.state.set('currentTree', event.currentTarget.dataset.tree);
                this.state.set('currentTreeId', event.currentTarget.dataset.treeId);
                this.treeAssociate.switchTree(event.currentTarget.dataset.treeId);

                this.renderCategorySwitcher();
            },

            /**
             * Change the current model when categories are checked/unchecked
             *
             * @param {Event} event
             */
            updateModel: function (event) {
                var selectedIds = _.filter(event.currentTarget.value.split(','), _.identity);
                this.state.set('selectedCategories', selectedIds);

                var rootTreeCode = this.state.get('currentTree');
                this.categoriesCount[rootTreeCode] =
                    this.$('li[data-code=' + rootTreeCode +  '] .jstree-checked').length;
                this.renderCategorySwitcher();

                var categoryCodes = _.map(selectedIds, this.getCategoryCode.bind(this));
                this.getFormModel().set('categories', categoryCodes);
                mediator.trigger('pim_enrich:form:entity:update_state');
            },

            /**
             * Initialize category count with hidden values
             */
            initCategoryCount: function () {
                _.each(this.trees, function (tree) {
                    var selectedCategories = [];
                    var hiddenSelection = this.$('#hidden-tree-input').val();
                    hiddenSelection = hiddenSelection.length > 0 ? hiddenSelection.split(',') : [];
                    _.each(hiddenSelection, function (categoryId) {
                        selectedCategories.push(this.cache[categoryId]);
                    }.bind(this));

                    this.categoriesCount[tree.code] = _.where(selectedCategories, {rootId: tree.id}).length;
                }.bind(this));
            },

            /**
             * Fetch category code from cache
             *
             * @param {integer} id
             *
             * @returns {string}
             */
            getCategoryCode: function (id) {
                if (!this.cache[id]) {
                    var $categoryElement = this.$('#node_' + id);
                    var $rootElement     = $categoryElement.closest('.root-unselectable');
                    this.cache[id] = {
                        code: String($categoryElement.data('code')),
                        rootId: $rootElement.data('tree-id')
                    };
                }

                return this.cache[id].code;
            },

            /**
             * Check if this extension is visible
             *
             * @returns {boolean}
             */
            isVisible: function () {
                return true;
            }
        });
    }
);

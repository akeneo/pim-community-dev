'use strict';
/**
 * Mass change category
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/i18n',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/mass-edit-form/product/operation',
        'pim/tree/associate',
        'pim/template/mass-edit/product/category'
    ],
    function (
        _,
        __,
        i18n,
        UserContext,
        FetcherRegistry,
        BaseOperation,
        TreeAssociate,
        template
    ) {
        return BaseOperation.extend({
            template: _.template(template),
            currentTree: null,
            categoryCache: {},
            selectedCategories: [],
            treePromise: null,
            view: null,
            trees: [],
            events: {
                'click .nav-tabs .tree-selector': 'changeTree',
                'change #hidden-tree-input': 'updateModel'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.trees = [];

                BaseOperation.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            reset: function () {
                this.setValue([]);

                this.treePromise        = null;
                this.currentTree        = null;
                this.selectedCategories = [];
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (null === this.treePromise) {
                    FetcherRegistry.getFetcher(this.config.fetcher).clear();
                    this.treePromise = FetcherRegistry
                        .getFetcher(this.config.fetcher)
                        .fetchAll()
                        .then(function (trees) {
                        this.trees = trees;

                        this.renderTrees(this.trees);

                        new TreeAssociate('#trees', '#hidden-tree-input', {
                            list_categories: this.config.listRoute,
                            children:        this.config.childrenRoute
                        });

                        this.delegateEvents();

                        return {
                            treeAssociate: new TreeAssociate('#trees', '#hidden-tree-input', {
                                list_categories: this.config.listRoute,
                                children:        this.config.childrenRoute
                            }),
                            trees: trees
                        };
                    }.bind(this));
                } else {
                    this.renderTrees(this.trees);

                    this.delegateEvents();
                }

                return this;
            },

            /**
             * Renders the current trees
             *
             * @param {Array} trees
             */
            renderTrees(trees) {
                if (null === this.currentTree) {
                    this.currentTree = _.first(trees).code;
                }

                this.$el.html(this.template({
                    i18n: i18n,
                    locale: UserContext.get('uiLocale'),
                    trees: trees,
                    currentTree: _.findWhere(trees, {code: this.currentTree}),
                    selectedCategories: this.selectedCategories,
                    readOnly: this.readOnly
                }));
            },

            /**
             * Update the mass edit model
             *
             * @param {Event} event
             */
            updateModel: function (event) {
                this.selectedCategories = event.target.value.split(',');
                this.setValue(_.map(this.selectedCategories, this.getCategoryCode.bind(this)));
            },

            /**
             * Update the model after dom event triggered
             *
             * @param {string} categories
             */
            setValue: function (categories) {
                let data = this.getFormData();

                data.actions = [{
                    field: 'categories',
                    value: categories
                }];

                this.setData(data);
            },

            /**
             * Get current value from mass edit model
             *
             * @return {string}
             */
            getValue: function () {
                const action = _.findWhere(this.getFormData().actions, {field: 'categories'});

                return action ? action.value : null;
            },

            /**
             * Change the current tree
             *
             * @param {Event} event
             */
            changeTree: function (event) {
                this.currentTree = event.currentTarget.dataset.tree;

                this.treePromise.then(function (elements) {
                    const tree = _.findWhere(elements.trees, {code: this.currentTree});

                    elements.treeAssociate.switchTree(tree.code);

                    this.delegateEvents();
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
                if (!this.categoryCache[id]) {
                    const $categoryElement = this.$('#node_' + id);
                    const $rootElement     = $categoryElement.closest('.root-unselectable');
                    this.categoryCache[id] = {
                        code: String($categoryElement.data('code')),
                        rootId: $rootElement.data('tree-id')
                    };
                }

                return this.categoryCache[id].code;
            }
        });
    }
);

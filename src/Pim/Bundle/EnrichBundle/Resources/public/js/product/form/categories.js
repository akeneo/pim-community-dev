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
        'backbone',
        'pim/form',
        'pim/template/product/tab/categories',
        'pim/template/product/tab/category-switcher',
        'pim/user-context',
        'routing',
        'pim/tree/associate',
        'oro/mediator'
    ],
    function ($,
              _,
              Backbone,
              BaseForm,
              formTemplate,
              switcherTemplate,
              UserContext,
              Routing,
              TreeAssociate,
              mediator
    ) {
        return BaseForm.extend({
            template: _.template(formTemplate),
            switcherTemplate: _.template(switcherTemplate),
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

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.state = new Backbone.Model();

                this.state.set('selectedCategories', []);

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    isVisible: this.isVisible.bind(this),
                    label: _.__('pim_enrich.form.product.tab.categories.title')
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.loadTrees().done(function (trees) {
                    var categoriesCount = {};
                    _.each(_.pluck(trees, 'id'), function (id) {
                        categoriesCount[id] = 0;
                    });

                    this.state.set('currentTree', _.first(trees).code);
                    this.state.set('currentTreeId', _.first(trees).id);

                    this.$el.html(
                        this.template({
                            product: this.getFormData(),
                            locale: UserContext.get('catalogLocale'),
                            state: this.state.toJSON(),
                            trees: trees
                        })
                    );

                    this.$el.find('.category-switcher').html(this.switcherTemplate({
                        state: this.state.toJSON(),
                        trees: trees,
                        categoriesCount: categoriesCount,
                        treeLinkSelector: this.treeLinkSelector
                    }));

                    this.treeAssociate = new TreeAssociate('#trees', '#hidden-tree-input', {
                        list_categories: 'pim_enrich_product_listcategories',
                        children:        'pim_enrich_categorytree_children'
                    });

                    this.delegateEvents();
                    this.initCategoryCount(trees);
                }.bind(this));

                return this;
            },

            /**
             * Load category trees
             *
             * @returns {promise}
             */
            loadTrees: function () {
                return $.getJSON(
                    Routing.generate('pim_enrich_product_category_rest_list', {id: this.getFormData().meta.id })
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

            changeTree: function (event) {
                this.state.set('currentTree', event.currentTarget.dataset.tree);
                this.state.set('currentTreeId', event.currentTarget.dataset.treeId);
                this.treeAssociate.switchTree(event.currentTarget.dataset.treeId);

                // TODO Use category switcher template
                $(event.currentTarget)
                    .find('.AknDropdown-menuLink').addClass('AknDropdown-menuLink--active').end()
                    .siblings('[data-tree]')
                    .each(function (i, link) {
                        $(link).find('.AknDropdown-menuLink').removeClass('AknDropdown-menuLink--active')
                    }).end();
                this.$el.find('.current-category:first').html($(event.currentTarget).find('.tree-label').text());
            },

            updateModel: function (event) {
                var selectedIds = _.filter(event.currentTarget.value.split(','), _.identity);
                this.state.set('selectedCategories', selectedIds);

                this.updateCategoryCount(this.state.get('currentTree'), this.state.get('currentTreeId'));

                var categoryCodes = _.map(selectedIds, this.getCategoryCode.bind(this));
                this.getFormModel().set('categories', categoryCodes);
                mediator.trigger('pim_enrich:form:entity:update_state');
            },

            /**
             * Initialize category count with hidden values
             *
             * @param {Array} trees
             */
            initCategoryCount: function (trees) {
                _.each(trees, function (tree) {
                    var selectedCategories = [];
                    var hiddenSelection = this.$('#hidden-tree-input').val();
                    hiddenSelection = hiddenSelection.length > 0 ? hiddenSelection.split(',') : [];
                    _.each(hiddenSelection, function (categoryId) {
                        selectedCategories.push(this.cache[categoryId]);
                    }.bind(this));
                    var categoryCount = _.where(selectedCategories, {rootId: tree.id}).length;
                    if (categoryCount > 0) {
                        $('#' + this.treeLinkSelector + tree.id).addClass(this.treeHasItemClass);
                    }
                    this.updateCategoryBadge(tree.code, categoryCount);
                }.bind(this));
            },

            /**
             * count selected leaves in the category jstree
             *
             * @param {String}  rootTreeCode
             * @param {integer} treeId
             */
            updateCategoryCount: function (rootTreeCode, treeId) {
                var $rootTreeContainer = this.$('li[data-code=' + rootTreeCode +  ']');
                var selected = $rootTreeContainer.find('.jstree-checked');

                if (selected.length > 0) {
                    $('#' + this.treeLinkSelector + treeId).addClass(this.treeHasItemClass);
                } else {
                    $('#' + this.treeLinkSelector + treeId).removeClass(this.treeHasItemClass);
                }

                this.updateCategoryBadge(rootTreeCode, selected.length);
            },

            /**
             * Update the category count badge
             *
             * @param {string} rootTreeCode
             * @param {integer} categoryCount
             */
            updateCategoryBadge: function (rootTreeCode, categoryCount) {
                var badge = this.$('li[data-tree=' + rootTreeCode +  ']').find('.AknBadge');
                badge.html(categoryCount);

                if (categoryCount > 0) {
                    badge.addClass('AknBadge--enabled').removeClass('AknBadge--grey');
                } else {
                    badge.removeClass('AknBadge--enabled').addClass('AknBadge--grey');
                }
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

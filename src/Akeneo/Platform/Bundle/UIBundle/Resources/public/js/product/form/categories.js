'use strict';
/**
 * Category tab extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
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
  'oro/mediator',
], function (
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
      'change #hidden-tree-input': 'updateModel',
    },
    treeAssociate: null,
    cache: {},
    trees: [],

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

      this.state.set('selectedCategories', {});

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
        code: undefined === this.config.tabCode ? this.code : this.config.tabCode,
        isVisible: this.isVisible.bind(this),
        label: __('pim_enrich.entity.category.plural_label'),
      });

      this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', this.handleLocaleChange.bind(this));

      return BaseForm.prototype.configure.apply(this, arguments);
    },

    handleLocaleChange: function ({context}) {
      if ('base_product' === context && null !== this.treeAssociate) {
        this.trees = [];
        this.render();
      }
    },

    /**
     * {@inheritdoc}
     */
    render: function () {
      if (null === this.treeAssociate || 0 === this.trees.length) {
        this.loadTrees().done(
          function (trees) {
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
                trees: this.trees,
              })
            );

            const lockedCategoryIds = this.getFormData().meta.ascendant_category_ids;

            this.treeAssociate = new TreeAssociate(
              {
                list_categories: this.config.itemCategoryListRoute,
                children: 'pim_enrich_categorytree_children',
              },
              this.isReadOnly(),
              lockedCategoryIds
            );

            this.initCategoryCount();
            this.renderCategorySwitcher();
          }.bind(this)
        );
      }
      this.delegateEvents();

      return this;
    },

    /**
     * Renders the category switcher in the main template
     */
    renderCategorySwitcher: function () {
      this.$el.find('.catalog-switcher:first').html(
        this.switcherTemplate({
          state: this.state.toJSON(),
          trees: this.trees,
          categoriesCount: this.categoriesCount,
          treeLinkSelector: this.treeLinkSelector,
          currentCategory: _.result(_.findWhere(this.trees, {code: this.state.toJSON().currentTree}), 'label'),
        })
      );
    },

    /**
     * Load category trees
     */
    loadTrees: function () {
      const params = {
        id: this.getFormData().meta.id,
        // Passing the locale as request parameter will force to refresh the current user locale in session
        // @see \Akeneo\UserManagement\Bundle\Context\UserContext::getCurrentLocale
        dataLocale: UserContext.get('catalogLocale'),
      };
      return $.getJSON(Routing.generate(this.config.itemCategoryTreeRoute, params)).then(
        function (data) {
          const selectedCategories = {};
          _.each(
            data.categories,
            function (category) {
              this.cache[category.code] = category;
              if (!selectedCategories[category.rootId]) {
                selectedCategories[category.rootId] = [];
              }
              selectedCategories[category.rootId].push(category.code);
            }.bind(this)
          );

          if (_.isEmpty(this.state.get('selectedCategories'))) {
            this.state.set('selectedCategories', selectedCategories);
          }

          return data.trees;
        }.bind(this)
      );
    },

    /**
     * Displays the current tree when the user choose another one
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
      var selectedCategoryCodesByTreeId = JSON.parse(event.currentTarget.value);
      this.state.set('selectedCategories', selectedCategoryCodesByTreeId);

      var rootTreeId = this.state.get('currentTreeId');
      this.categoriesCount[rootTreeId] = selectedCategoryCodesByTreeId[rootTreeId].length;
      this.renderCategorySwitcher();

      var allTreesCategoryCodes = [];
      Object.values(selectedCategoryCodesByTreeId).forEach(categoryCodes => {
        allTreesCategoryCodes = allTreesCategoryCodes.concat(categoryCodes);
      });
      this.getFormModel().set('categories', allTreesCategoryCodes);
      mediator.trigger('pim_enrich:form:entity:update_state');
    },

    /**
     * Initialize category count with hidden values
     */
    initCategoryCount: function () {
      this.categoriesCount = {};
      Object.keys(this.state.get('selectedCategories')).forEach(treeId => {
        this.categoriesCount[treeId] = this.state.get('selectedCategories')[treeId].length;
      });
    },

    /**
     * Check if this extension is visible
     *
     * @returns {boolean}
     */
    isVisible: function () {
      return true;
    },

    isReadOnly: function () {
      return false;
    },
  });
});

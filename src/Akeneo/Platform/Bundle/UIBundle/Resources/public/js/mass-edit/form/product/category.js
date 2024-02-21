'use strict';
/**
 * Mass change category
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
  'jquery',
  'underscore',
  'oro/translator',
  'oro/messenger',
  'pim/i18n',
  'pim/user-context',
  'pim/fetcher-registry',
  'pim/mass-edit-form/product/operation',
  'pim/tree/associate',
  'pim/common/property',
  'pim/template/mass-edit/product/category',
], function (
  $,
  _,
  __,
  messenger,
  i18n,
  UserContext,
  FetcherRegistry,
  BaseOperation,
  TreeAssociate,
  propertyAccessor,
  template
) {
  return BaseOperation.extend({
    template: _.template(template),
    currentTree: null,
    categoryCache: {},
    selectedCategories: {},
    treePromise: null,
    view: null,
    trees: [],
    events: {
      'click .nav-tabs .tree-selector': 'changeTree',
      'change #hidden-tree-input': 'updateModel',
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

      this.treePromise = null;
      this.currentTree = null;
      this.selectedCategories = {};
    },

    /**
     * {@inheritdoc}
     */
    render: function () {
      if (null === this.treePromise) {
        FetcherRegistry.getFetcher(this.config.fetcher).clear();
        this.treePromise = FetcherRegistry.getFetcher(this.config.fetcher)
          .fetchAll()
          .then(
            function (trees) {
              this.trees = trees;

              if (null === this.currentTree) {
                this.currentTree = _.first(trees).code;
              }

              this.$el.html(
                this.template({
                  i18n: i18n,
                  locale: UserContext.get('catalogLocale'),
                  trees: trees,
                  currentTree: _.findWhere(trees, {code: this.currentTree}),
                  selectedCategories: this.selectedCategories,
                })
              );

              this.delegateEvents();

              this.toggleContentCache();

              return {
                treeAssociate: new TreeAssociate({
                  list_categories: this.config.listRoute,
                  children: this.config.childrenRoute,
                }),
                trees: trees,
              };
            }.bind(this)
          );
      } else {
        this.toggleContentCache();

        this.delegateEvents();
      }

      return this;
    },

    /**
     * In this method, we don't re-render the trees because select elements on several trees is hell.
     * We simply hide or show the cache to avoid clicking on new elements during the confirm.
     **/
    toggleContentCache: function () {
      if (this.readOnly) {
        this.$el.find('.content-cache').removeClass('AknTabContainer-contentCache--hidden');
      } else {
        this.$el.find('.content-cache').addClass('AknTabContainer-contentCache--hidden');
      }
    },

    /**
     * Update the mass edit model
     *
     * @param {Event} event
     */
    updateModel: function (event) {
      const selectedCategoryCodesByTreeId = JSON.parse(event.currentTarget.value);
      let allTreesCategoryCodes = [];
      Object.values(selectedCategoryCodesByTreeId).forEach(categoryCodes => {
        allTreesCategoryCodes = allTreesCategoryCodes.concat(categoryCodes);
      });
      this.setValue(allTreesCategoryCodes);
    },

    /**
     * Update the model after dom event triggered
     *
     * @param {string} categories
     */
    setValue: function (categories) {
      let data = this.getFormData();

      data.actions = [
        {
          field: 'categories',
          value: categories,
        },
      ];

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

      this.treePromise.then(
        function (elements) {
          const tree = _.findWhere(elements.trees, {code: this.currentTree});

          elements.treeAssociate.switchTree(tree.id);
          $('.tree-selector').removeClass('active').removeClass('AknHorizontalNavtab-item--active');
          $(`.tree-selector[data-tree=${tree.code}]`).addClass('active').addClass('AknHorizontalNavtab-item--active');

          this.delegateEvents();
        }.bind(this)
      );
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
        const $rootElement = $categoryElement.closest('.root-unselectable');
        this.categoryCache[id] = {
          code: String($categoryElement.data('code')),
          rootId: $rootElement.data('tree-id'),
        };
      }

      return this.categoryCache[id].code;
    },

    /**
     * Checks there is at least one category selected to go to the next step
     */
    validate: function () {
      const data = this.getFormData();
      const categories = propertyAccessor.accessProperty(data, 'actions.0.value', []);

      const hasUpdates = 0 !== categories.length;

      if (!hasUpdates) {
        messenger.notify('error', __(`pim_enrich.mass_edit.product.operation.${data.operation}.no_update`));
      }

      return $.Deferred().resolve(hasUpdates);
    },
  });
});

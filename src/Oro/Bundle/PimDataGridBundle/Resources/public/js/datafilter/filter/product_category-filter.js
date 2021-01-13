define(['jquery', 'underscore', 'oro/datafilter/number-filter', 'pim/tree/view', 'oro/mediator'], function (
  $,
  _,
  NumberFilter,
  TreeView,
  mediator
) {
  'use strict';

  /**
   * Category filter
   *
   * @author    Filips Alpe <filips@akeneo.com>
   * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
   * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
   *
   * @export  oro/datafilter/product_category-filter
   * @class   oro.datafilter.CategoryFilter
   * @extends oro.datafilter.NumberFilter
   */
  return NumberFilter.extend({
    /**
     * @inheritDoc
     */
    emptyValue: {
      value: {
        treeId: 0,
        categoryId: -2,
      },
      type: 1,
    },

    /**
     * @inheritDoc
     */
    value: {},

    treeView: null,

    /**
     * @inheritDoc
     */
    initialize: function (urlParams, gridName, categoryBaseRoute, container, updateCallback) {
      this.$el.remove();
      this.$el = $(container);

      this.value = $.extend(true, {}, this.emptyValue);

      NumberFilter.prototype.initialize.apply(this, arguments);

      if (urlParams && urlParams[gridName + '[_filter][category][value][treeId]']) {
        this.value.value.treeId = parseInt(urlParams[gridName + '[_filter][category][value][treeId]']);
      }
      if (urlParams && urlParams[gridName + '[_filter][category][value][categoryId]']) {
        this.value.value.categoryId = parseInt(urlParams[gridName + '[_filter][category][value][categoryId]']);
      }

      const onTreeUpdated = (treeLabel, categoryLabel) => {
        this.trigger('update_label', treeLabel, categoryLabel);
      };

      this.$el[0].addEventListener('tree.updated', event => {
        this._onTreeUpdated();
        event.preventDefault();
      });

      this.treeView = new TreeView(
        this.$el[0],
        this._getInitialState(),
        {
          listTree: `${categoryBaseRoute}_listtree`,
          children: `${categoryBaseRoute}_children`,
        },
        onTreeUpdated
      );

      this.listenTo(mediator, 'datagrid_filters:build.post', function (filtersManager) {
        this.listenTo(filtersManager, 'collection-filters:createState.post', function (filtersState) {
          _.extend(filtersState, {category: this._getTreeState()});
        });
        filtersManager.listenTo(this, 'update', filtersManager._onFilterUpdated);
      });

      this.listenTo(mediator, 'filters-column:init', () => {
        mediator.trigger('filters-column:update-filter', {category: this._getTreeState()}, true);

        this.listenTo(this, 'update', () => {
          mediator.trigger('filters-column:update-filter', {category: this._getTreeState()});
        });
      });

      mediator.on('grid_action_execute:product-grid:delete', function () {
        // TODO
        this.treeView.refresh();
      });

      if (undefined !== updateCallback) {
        updateCallback(this.value);
      }
    },

    /**
     * Get the current tree state
     */
    _getTreeState: function () {
      if (!this.$el.is(':visible')) {
        return this.emptyValue;
      }

      var state = this.treeView.getState();

      return {
        value: {
          treeId: state.selectedTree,
          categoryId: state.selectedNode,
        },
        type: +state.includeSub,
      };
    },

    getValue: function () {
      if (!this.$el.is(':visible')) {
        return this.emptyValue;
      }

      return NumberFilter.prototype.getValue.apply(this, arguments);
    },

    /**
     * Get initial state for the tree
     */
    _getInitialState: function () {
      return {
        selectedNode: +this.value.value.categoryId,
        selectedTree: +this.value.value.treeId,
        includeSub: !!this.value.type,
      };
    },

    /**
     * Sync the tree state with the filter value
     */
    _updateState: function () {
      this.value = this._getTreeState();
    },

    /**
     * On tree updated
     */
    _onTreeUpdated: function () {
      if (!_.isEqual(this.value, this._getTreeState())) {
        this._updateState();
        this._triggerUpdate();
      }
    },

    /**
     * @inheritDoc
     */
    _triggerUpdate: function () {
      this.trigger('update', this.value);
    },

    /**
     * @inheritDoc
     */
    isEmpty: function () {
      return _.isEqual(this.emptyValue, this._getTreeState());
    },

    /**
     * @inheritDoc
     */
    reset: function () {
      TreeView.reset();
      NumberFilter.prototype.reset.apply(this, arguments);
    },
  });
});

/**
 * Scope filter
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @export  oro/datafilter/product_scope-filter
 * @class   oro.datafilter.ScopeFilter
 * @extends oro.datafilter.SelectFilter
 */

define([
  'jquery',
  'underscore',
  'oro/mediator',
  'oro/datafilter/select-filter',
  'pim/user-context',
  'pim/datagrid/state',
  'oro/app',
  'pim/template/datagrid/filter/scope-filter',
], function ($, _, mediator, SelectFilter, UserContext, DatagridState, app, template) {
  'use strict';

  return SelectFilter.extend({
    template: _.template(template),
    className: 'AknDropdown AknFilterBox-filterContainer filter-select filter-criteria-selector scope-switcher',
    events: {
      'keydown select': '_preventEnterProcessing',
      'click .AknDropdown-menuLink': '_onSelectChange',
    },
    /**
     * @override
     * @property {Boolean}
     * @see Oro.Filter.SelectFilter
     */
    contextSearch: false,
    catalogScope: null,

    initialize: function () {
      SelectFilter.prototype.initialize.apply(this, arguments);
      this.catalogScope = UserContext.get('catalogScope');

      mediator.once('datagrid_filters:rendered', this.resetValue.bind(this));
      mediator.once('datagrid_filters:rendered', this.moveFilter.bind(this));

      mediator.bind('grid_load:complete', function (collection) {
        $('#grid-' + collection.inputName)
          .find('div.toolbar')
          .show();
      });
    },

    /**
     * Move the filter to its proper position
     *
     * @param {Array} collection
     */
    moveFilter: function (collection) {
      // Fix the issue that it display the filter on each switch from published and products
      // @see PIM-10218
      // @todo Handle the scope as the locale/category filters modules
      const $previousScopeFilter = $('[data-drop-zone="column-context-switcher"] [data-type="product_scope"]');

      if ($previousScopeFilter.length > 0) {
        $previousScopeFilter.detach();
      }

      this.$el.prependTo($('[data-drop-zone="column-context-switcher"]'));

      let $grid = $('#grid-' + collection.inputName);

      if (0 === $grid.length) {
        $grid = $('[data-type="datagrid"]:first');
      }

      let $filterChoices = $grid.find('#add-filter-select');
      $filterChoices.find('option[value="scope"]').remove();
      $filterChoices.multiselect('refresh');
    },

    /**
     * Update the current filter value using the UserContext.
     */
    resetValue: function () {
      var scope = DatagridState.get('product-grid', 'scope');
      if (!scope) {
        scope = this.catalogScope;
        this.setValue({value: scope});
      }

      UserContext.set('catalogScope', scope);

      this.selectWidget.multiselect('refresh');

      this.render();
    },

    /**
     * Updates the current scope without refreshing the datagrid
     */
    setValueSilent: function (value) {
      if (this._isNewValueUpdated(value)) {
        this.value = app.deepClone(value);
        this._updateDOMValue();
      }
      return this;
    },

    /**
     * {@inheritdoc}
     */
    render: function () {
      SelectFilter.prototype.render.apply(this, arguments);

      this.highlightScope(this.catalogScope);
    },

    /**
     * @inheritDoc
     */
    disable: function () {
      return this;
    },

    /**
     * @inheritDoc
     */
    hide: function () {
      return this;
    },

    /**
     * @inheritDoc
     *
     * Override to save the scope into the product grid state.
     *
     * We don't put this logic in the setValue method because we want this behavior only when the value
     * comes from a change of the select element, not from a view/url for example.
     */
    _onSelectChange: function (event) {
      const value = $(event.target).closest('.AknDropdown-menuLink').attr('data-value');
      this.highlightScope(value);

      UserContext.set('catalogScope', value);
      this.catalogScope = value;

      SelectFilter.prototype._onSelectChange.apply(this, arguments);

      DatagridState.set('product-grid', 'scope', value);
    },

    /**
     * @inheritDoc
     */
    _readDOMValue() {
      const currentScope = this.$el.find('.AknDropdown-menuLink--active').attr('data-value');

      return {value: currentScope};
    },

    /**
     * Highlight the current scope
     *
     * @param scope
     */
    highlightScope(scope) {
      const currentOption = _.findWhere(this.options.choices, {value: scope});
      if (undefined !== currentOption) {
        this.$el.find('.value').html(currentOption.label);
        this.$el.find('.AknDropdown-menuLink').removeClass('AknDropdown-menuLink--active');
        this.$el.find('.AknDropdown-menuLink[data-value="' + scope + '"]').addClass('AknDropdown-menuLink--active');
      }
    },
  });
});

/**
 * Save extension for the Datagrid View Selector.
 * It displays a button near the selector to allow the user to save the current changes
 * to the current view.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import template from 'pim/template/grid/view-selector/save-view'
import DatagridState from 'pim/datagrid/state'
import UserContext from 'pim/user-context'
import DatagridViewSaver from 'pim/saver/datagrid-view'
import messenger from 'oro/messenger'

export default BaseForm.extend({
  template: _.template(template),
  tagName: 'span',
  className: 'save-button',
  events: {
    'click .save': 'saveView'
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'grid:view-selector:state-changed', this.onDatagridStateChange)

    return BaseForm.prototype.configure.apply(this, arguments)
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (this.getRoot().currentViewType !== 'view' ||
      UserContext.get('meta').id !== this.getRoot().currentView.owner_id
    ) {
      this.$el.html('')

      return
    }

    this.$el.html(this.template({
      dirty: this.dirty,
      label: __('grid.view_selector.save_changes')
    }))

    this.$('[data-toggle="tooltip"]').tooltip()
  },

  /**
   * Method called on datagrid state change (when columns or filters are modified)
   *
   * @param {Object} datagridState
   */
  onDatagridStateChange: function (datagridState) {
    var initialView = this.getRoot().initialView
    var initialViewExists = initialView !== null && initialView.id !== 0

    if (initialViewExists) {
      var filtersModified = initialView.filters !== datagridState.filters
      var columnsModified = !_.isEqual(initialView.columns, datagridState.columns.split(','))

      this.dirty = filtersModified || columnsModified
      this.render()
    }
  },

  /**
   * Save the current Datagrid view in database and triggers an event to the parent
   * to select it.
   */
  saveView: function () {
    var gridState = DatagridState.get(this.getRoot().gridAlias, ['filters', 'columns'])

    var currentView = $.extend(true, {}, this.getRoot().currentView)
    currentView.filters = gridState.filters
    currentView.columns = gridState.columns

    DatagridViewSaver.save(currentView, this.getRoot().gridAlias)
      .done(function (response) {
        this.getRoot().trigger('grid:view-selector:view-saved', response.id)
      }.bind(this))
      .fail(function (response) {
        _.each(response.responseJSON, function (error) {
          messenger.notify('error', error)
        })
      })
  }
})

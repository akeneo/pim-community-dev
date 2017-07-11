
/**
 * Create extension for the Datagrid View Selector.
 * It displays a button near the selector to allow the user to create a new view.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import Backbone from 'backbone'
import BaseForm from 'pim/form'
import template from 'pim/template/grid/view-selector/create-view'
import templateInput from 'pim/template/grid/view-selector/create-view-label-input'
import DatagridState from 'pim/datagrid/state'
import DatagridViewSaver from 'pim/saver/datagrid-view'
import messenger from 'oro/messenger'
export default BaseForm.extend({
  template: _.template(template),
  templateInput: _.template(templateInput),
  tagName: 'span',
  className: 'create-button',
  events: {
    'click .create': 'promptCreateView'
  },

            /**
             * {@inheritdoc}
             */
  render: function () {
    if (this.getRoot().currentViewType !== 'view') {
      this.$el.html('')

      return this
    }

    this.$el.html(this.template({
      label: __('grid.view_selector.create_view')
    }))

    this.$('[data-toggle="tooltip"]').tooltip()

    return this
  },

            /**
             * Prompt the view creation modal.
             */
  promptCreateView: function () {
    this.getRoot().trigger('grid:view-selector:close-selector')

    var modal = new Backbone.BootstrapModal({
      title: __('grid.view_selector.choose_label'),
      content: this.templateInput({placeholder: __('grid.view_selector.placeholder')}),
      okText: __('pim_datagrid.view_selector.create_view_modal.confirm'),
      cancelText: __('pim_datagrid.view_selector.create_view_modal.cancel')
    })
    modal.open()

    var $submitButton = modal.$el.find('.ok').hide()

    modal.on('ok', this.saveView.bind(this, modal))
    modal.on('cancel', function () {
      modal.remove()
    })
    modal.$('input[name="new-view-label"]').on('input', function (event) {
      var label = event.target.value

      if (!label.length) {
        $submitButton.hide()
      } else {
        $submitButton.show()
      }
    })
    modal.$('input[name="new-view-label"]').on('keypress', function (event) {
      if ((event.keyCode || event.which) === 13 && event.target.value.length) {
        $submitButton.trigger('click')
      }
    })
  },

            /**
             * Save the current Datagrid view in database and triggers an event to the parent
             * to select it.
             *
             * @param {object} modal
             */
  saveView: function (modal) {
    var gridState = DatagridState.get(this.getRoot().gridAlias, ['filters', 'columns'])
    var newView = {
      filters: gridState.filters,
      columns: gridState.columns,
      label: modal.$('input[name="new-view-label"]').val()
    }

    DatagridViewSaver.save(newView, this.getRoot().gridAlias)
                    .done(function (response) {
                      this.getRoot().trigger('grid:view-selector:view-created', response.id)
                    }.bind(this))
                    .fail(function (response) {
                      _.each(response.responseJSON, function (error) {
                        messenger.notify('error', error)
                      })
                    })
  }
})

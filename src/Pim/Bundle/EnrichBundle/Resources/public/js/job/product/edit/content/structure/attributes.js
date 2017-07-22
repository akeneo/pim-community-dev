/**
 * Attributes structure filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import Backbone from 'backbone'
import template from 'pim/template/export/product/edit/content/structure/attributes'
import BaseForm from 'pim/form'
import LoadingMask from 'oro/loading-mask'
import AttributeSelector from 'pim/job/product/edit/content/structure/attributes-selector'

export default BaseForm.extend({
  className: 'AknFieldContainer attributes',
  template: _.template(template),
  events: {
    'click button': 'openSelector'
  },

  /**
   * Initializes configuration.
   *
   * @param {Object} config
   */
  initialize: function (config) {
    this.config = config.config

    return BaseForm.prototype.initialize.apply(this, arguments)
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this
    }

    var attributes = this.getFilters().structure.attributes || []

    this.$el.html(
      this.template({
        __: __,
        isEditable: this.isEditable(),
        titleEdit: __('pim_enrich.export.product.filter.attributes.title'),
        labelEdit: __('pim_enrich.export.product.filter.attributes.edit'),
        labelInfo: __(
          'pim_enrich.export.product.filter.attributes.label',
          {
            count: attributes.length
          },
          attributes.length
        ),
        errors: this.getParent().getValidationErrorsForField('attributes')
      })
    )

    this.delegateEvents()

    this.$('[data-toggle="tooltip"]').tooltip()
    this.renderExtensions()
  },

  /**
   * Returns whether this filter is editable.
   *
   * @returns {boolean}
   */
  isEditable: function () {
    return undefined !== this.config.readOnly
      ? !this.config.readOnly
      : true
  },

  openSelector: function (e) {
    e.preventDefault()
    var loadingMask = new LoadingMask()
    loadingMask.render().$el.appendTo(this.getRoot().$el)
    loadingMask.show()
    var selectedAttributes = this.getFilters().structure.attributes || []
    var attributeSelector = new AttributeSelector()
    attributeSelector.setSelected(selectedAttributes)

    var modal = new Backbone.BootstrapModal({
      className: 'modal modal-large column-configurator-modal',
      modalOptions: {
        backdrop: 'static',
        keyboard: false
      },
      allowCancel: true,
      okCloses: false,
      cancelText: _.__('pim_enrich.export.product.filter.attributes.modal.cancel'),
      title: _.__('pim_enrich.export.product.filter.attributes.modal.title'),
      content: '<div class="AknColumnConfigurator attribute-selector"></div>',
      okText: _.__('pim_enrich.export.product.filter.attributes.modal.apply')
    })

    loadingMask.hide()
    loadingMask.$el.remove()

    modal.open()
    attributeSelector.setElement('.attribute-selector').render()

    modal.on('ok', function () {
      var values = attributeSelector.getSelected()
      var data = this.getFilters()

      data.structure.attributes = values

      this.setData(data)
      modal.close()
      this.render()
    }.bind(this))
  },

  /**
   * Get filters
   *
   * @return {object}
   */
  getFilters: function () {
    return this.getFormData().configuration.filters
  }
})

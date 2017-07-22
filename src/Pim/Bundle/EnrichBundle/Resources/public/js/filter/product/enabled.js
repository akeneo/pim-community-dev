
// Should be reworked to be a boolean filter

import _ from 'underscore'
import __ from 'oro/translator'
import BaseFilter from 'pim/filter/filter'
import Routing from 'routing'
import template from 'pim/template/filter/product/enabled'
import 'pim/fetcher-registry'
import 'pim/user-context'
import 'pim/i18n'
import 'jquery.select2'
export default BaseFilter.extend({
  shortname: 'enabled',
  template: _.template(template),
  removable: false,
  events: {
    'change [name="filter-value"]': 'updateState'
  },

  /**
   * {@inherit}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', function (data) {
      _.defaults(data, {
        field: this.getCode(),
        operator: '=',
        value: true
      })
    }.bind(this))

    return BaseFilter.prototype.configure.apply(this, arguments)
  },

  /**
   * Returns rendered input.
   *
   * @return {String}
   */
  renderInput: function () {
    return this.template({
      isEditable: this.isEditable(),
      labels: {
        title: __('pim_enrich.export.product.filter.enabled.title'),
        valueChoices: {
          all: __('pim_enrich.export.product.filter.enabled.value.all'),
          enabled: __('pim_enrich.export.product.filter.enabled.value.enabled'),
          disabled: __('pim_enrich.export.product.filter.enabled.value.disabled')
        }
      },
      value: this.getValue()
    })
  },

  /**
   * Initializes select2 after rendering.
   */
  postRender: function () {
    this.$('[name="filter-value"]').select2({
      minimumResultsForSearch: -1
    })
  },

  /**
   * {@inheritdoc}
   */
  isEmpty: function () {
    return false
  },

  /**
   * Updates operator and value on fields change.
   */
  updateState: function () {
    var value = this.$('[name="filter-value"]').val()

    if (value === 'all') {
      this.setData({
        field: this.getField(),
        operator: 'ALL',
        value: null
      })
    } else {
      this.setData({
        field: this.getField(),
        operator: '=',
        value: value === 'enabled'
      })
    }
  }
})

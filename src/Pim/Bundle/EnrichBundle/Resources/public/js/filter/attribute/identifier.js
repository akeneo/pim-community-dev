
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import BaseFilter from 'pim/filter/filter'
import FetcherRegistry from 'pim/fetcher-registry'
import UserContext from 'pim/user-context'
import template from 'pim/template/filter/product/identifier'
export default BaseFilter.extend({
  shortname: 'identifier',
  template: _.template(template),
  events: {
    'change [name="filter-value"]': 'updateState'
  },

        /**
         * {@inheritdoc}
         */
  isEmpty: function () {
    return _.isEmpty(this.getValue())
  },

        /**
         * {@inheritdoc}
         */
  renderInput: function () {
    return this.template({
      __: __,
      value: _.isArray(this.getValue()) ? this.getValue().join(', ') : '',
      field: this.getField(),
      isEditable: this.isEditable()
    })
  },

        /**
         * {@inheritdoc}
         */
  getTemplateContext: function () {
    return BaseFilter.prototype.getTemplateContext.apply(this, arguments)
                .then(function (templateContext) {
                  return _.extend({}, templateContext, {
                    removable: false
                  })
                })
  },

        /**
         * {@inheritdoc}
         */
  updateState: function () {
    var value = this.$('[name="filter-value"]').val().split(/[\s,]+/)
    var cleanedValues = _.reject(value, function (val) {
      return val === ''
    })

    this.setData({
      field: this.getField(),
      operator: 'IN',
      value: cleanedValues
    })
  }
})

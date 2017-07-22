import _ from 'underscore'
import __ from 'oro/translator'
import BaseFilter from 'pim/filter/attribute/attribute'
import template from 'pim/template/filter/attribute/string'
import 'jquery.select2'

export default BaseFilter.extend({
  shortname: 'string',
  template: _.template(template),
  events: {
    'change [name="filter-operator"], [name="filter-value"]': 'updateState'
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', function (data) {
      _.defaults(data, {
        field: this.getCode(),
        value: '',
        operator: _.first(this.config.operators)
      })
    }.bind(this))

    return BaseFilter.prototype.configure.apply(this, arguments)
  },

  /**
   * {@inherit}
   */
  isEmpty: function () {
    return !_.contains(['EMPTY', 'NOT EMPTY'], this.getOperator()) &&
      (undefined === this.getValue() || this.getValue() === '')
  },

  /**
   * {@inherit}
   */
  renderInput: function (templateContext) {
    return this.template(_.extend({}, templateContext, {
      __: __,
      value: this.getValue(),
      field: this.getField(),
      operator: this.getOperator(),
      operators: this.config.operators
    }))
  },

  /**
   * {@inheritdoc}
   */
  postRender: function () {
    this.$('.operator').select2({
      minimumResultsForSearch: -1
    })
  },

  /**
   * {@inherit}
   */
  updateState: function () {
    var value = this.$('[name="filter-value"]').val()
    var operator = this.$('[name="filter-operator"]').val()

    this.setData({
      field: this.getField(),
      operator: operator,
      value: value
    })

    this.render()
  }
})

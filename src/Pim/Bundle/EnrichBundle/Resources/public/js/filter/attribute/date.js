
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import BaseFilter from 'pim/filter/attribute/attribute'
import template from 'pim/template/filter/attribute/date'
import Datepicker from 'datepicker'
import DateFormatter from 'pim/formatter/date'
import DateContext from 'pim/date-context'
import 'jquery.select2'
export default BaseFilter.extend({
  shortname: 'date',
  template: _.template(template),
  events: {
    'change [name^="filter-"]': 'updateState'
  },

  /**
   * Date widget options
   */
  datetimepickerOptions: {
    format: DateContext.get('date').format,
    defaultFormat: DateContext.get('date').defaultFormat,
    language: DateContext.get('language')
  },

  /**
   * Model date format
   */
  modelDateFormat: 'yyyy-MM-dd',

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', function (data) {
      _.defaults(data, {
        field: this.getCode(),
        operator: _.first(_.values(this.config.operators))
      })
    }.bind(this))

    return BaseFilter.prototype.configure.apply(this, arguments)
  },

  /**
   * {@inherit}
   */
  isEmpty: function () {
    var value = this.getValue()

    if (_.contains(['BETWEEN', 'NOT BETWEEN'], this.getOperator()) &&
      (undefined === value || !_.isArray(value) || _.isEmpty(value[0]) || _.isEmpty(value[1]))
    ) {
      return true
    }

    if (!_.contains(['EMPTY', 'NOT EMPTY'], this.getOperator()) &&
      (undefined === value || value === '')
    ) {
      return true
    }

    return false
  },

  /**
   * Initializes select2 and datepicker after rendering.
   */
  postRender: function () {
    var startDate = this.$('.start-date-wrapper:first')
    var endDate = this.$('.end-date-wrapper:first')

    this.$('[name="filter-operator"]').select2()

    if (startDate.length !== 0) {
      Datepicker
        .init(startDate, this.datetimepickerOptions)
        .on('changeDate', this.updateState.bind(this))
    }
    if (endDate.length !== 0) {
      Datepicker
        .init(endDate, this.datetimepickerOptions)
        .on('changeDate', this.updateState.bind(this))
    }
  },

  /**
   * {@inherit}
   */
  renderInput: function () {
    var dateFormat = DateContext.get('date').format
    var value = this.getValue()
    var startValue = DateFormatter.format(value, this.modelDateFormat, dateFormat)
    var endValue = null

    if (_.isArray(value)) {
      startValue = DateFormatter.format(value[0], this.modelDateFormat, dateFormat)
      endValue = DateFormatter.format(value[1], this.modelDateFormat, dateFormat)
    }

    return this.template({
      isEditable: this.isEditable(),
      __: __,
      shortName: this.shortname,
      field: this.getField(),
      operator: this.getOperator(),
      startValue: startValue,
      endValue: endValue,
      operatorChoices: this.config.operators
    })
  },

  /**
   * {@inherit}
   */
  updateState: function () {
    this.$('.start-date-wrapper').datetimepicker('hide')
    this.$('.end-date-wrapper').datetimepicker('hide')

    var value = null
    var operator = this.$('[name="filter-operator"]').val()

    if (!_.contains(['EMPTY', 'NOT EMPTY'], operator)) {
      var dateFormat = DateContext.get('date').format
      var startValue = this.$('[name="filter-value-start"]').val()
      var formattedStartVal = DateFormatter.format(startValue, dateFormat, this.modelDateFormat)
      var valueEndField = this.$('[name="filter-value-end"]')

      value = formattedStartVal

      if (valueEndField.length !== 0) {
        var endValue = valueEndField.val()
        var formattedEndVal = DateFormatter.format(endValue, dateFormat, this.modelDateFormat)

        value = [formattedStartVal, formattedEndVal]
      }
    }

    this.setData({
      field: this.getField(),
      operator: operator,
      value: value
    })

    this.render()
  }
})

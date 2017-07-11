
import _ from 'underscore'
import Backbone from 'backbone'
import template from 'pim/template/error/error'
export default Backbone.View.extend({
  template: _.template(template),
  initialize: function (message, statusCode) {
    this.message = message
    this.statusCode = statusCode
  },
  render: function () {
    this.$el.html(this.template({
      message: this.message,
      statusCode: this.statusCode
    }))
  }
})

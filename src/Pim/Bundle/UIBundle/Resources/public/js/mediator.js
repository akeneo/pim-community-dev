/* global define */
import _ from 'underscore'
import Backbone from 'backbone'

    /**
     * @export oro/mediator
     * @name   oro.mediator
     */
export default _.extend({
  clear: function (namespace) {
    this._events = _.omit(this._events, function (events, code) {
      return code.indexOf(namespace) === 0
    })

    _.each(this._events, _.bind(function (events, index) {
      var filtredEvents = []
      _.each(events, function (event) {
        if (!_.isString(event.context) || event.context.indexOf(namespace) !== 0) {
          filtredEvents.push(event)
        }
      })

      this._events[index] = filtredEvents
    }, this))
  }
}, Backbone.Events)

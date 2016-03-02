/* global define */
define(['underscore', 'backbone'],
function (_, Backbone) {
    'use strict';

    /**
     * @export oro/mediator
     * @name   oro.mediator
     */
    return _.extend({
        clear: function (namespace) {
            this._events = _.omit(this._events, function (events, code) {
                return 0 === code.indexOf(namespace);
            });

            _.each(this._events, _.bind(function (events, index) {
                var filtredEvents = [];
                _.each(events, function (event) {
                    if (!_.isString(event.context) || 0 !== event.context.indexOf(namespace)) {
                        filtredEvents.push(event);
                    }
                });

                this._events[index] = filtredEvents;
            }, this));
        }
    }, Backbone.Events);
});

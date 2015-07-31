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
            this._events = _.omit(this._events, function (event, code) {
                return 0 === code.indexOf(namespace);
            });
        }
    }, Backbone.Events);
});

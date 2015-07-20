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
            this._events = _.pick(this._events, _.reject(_.keys(this._events), function (code) {
                return 0 === code.indexOf(namespace);
            }));
        }
    }, Backbone.Events);
});

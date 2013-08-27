/* global define */
define(['underscore', 'backbone'],
function(_, Backbone) {
    'use strict';

    /**
     * @export oro/mediator
     */
    return _.extend({}, Backbone.Events);
});

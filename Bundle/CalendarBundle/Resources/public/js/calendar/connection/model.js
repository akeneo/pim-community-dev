/* global define */
define(['backbone', 'oro/calendar/connection/collection'],
function(Backbone, ConnectionCollection) {
    'use strict';

    /**
     * @export  oro/calendar/connection/model
     * @class   oro.calendar.connection.Model
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        /** @property */
        idAttribute: 'calendar',

        collection: ConnectionCollection,

        defaults: {
            color : null,
            backgroundColor : null,
            calendar: null,
            calendarName: null,
            owner: null,
            removable: false
        }
    });
});

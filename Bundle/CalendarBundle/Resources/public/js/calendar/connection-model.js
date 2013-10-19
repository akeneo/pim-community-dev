/* global define */
define(['backbone', 'oro/calendar/connection-collection'],
    function(Backbone, CalendarConnectionCollection) {
        'use strict';

        /**
         * @export  oro/calendar/connection-model
         * @class   oro.calendar.CalendarConnectionModel
         * @extends Backbone.Model
         */
        return Backbone.Model.extend({
            /** @property */
            idAttribute: 'calendar',

            collection: CalendarConnectionCollection,

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

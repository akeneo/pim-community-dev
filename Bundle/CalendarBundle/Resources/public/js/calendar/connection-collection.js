/* global define */
define(['backbone', 'routing', 'oro/calendar/connection-model'],
    function(Backbone, routing, CalendarConnectionModel) {
        'use strict';

        /**
         * @export  oro/calendar/connection-collection
         * @class   oro.calendar.CalendarConnectionCollection
         * @extends Backbone.Collection
         */
        return Backbone.Collection.extend({
            route: 'oro_api_get_calendar_connections',
            url: null,
            model: CalendarConnectionModel,

            /**
             * Sets a calendar this collection works with
             *
             * @param {int} calendarId
             */
            setCalendar: function (calendarId) {
                this.url = routing.generate(this.route, {id: calendarId});
            }
        });
    });

/* global define */
define(['backbone', 'routing', 'oro/calendar/event-model'],
    function(Backbone, routing, CalendarEvent) {
        'use strict';

        /**
         * @export  oro/calendar/event-collection
         * @class   oro.calendar.CalendarEventCollection
         * @extends Backbone.Collection
         */
        return Backbone.Collection.extend({
            route: 'oro_api_get_calendarevents',
            url: null,
            model: CalendarEvent,
            calendar: null,
            subordinate: false,

            setRange: function (start, end) {
                this.url = routing.generate(
                    this.route,
                    {calendar: this.calendar, start: start, end: end, subordinate: this.subordinate}
                );
            },
            setCalendar: function(calendarId) {
                this.calendar = calendarId;
            },
            getCalendar: function() {
                return this.calendar;
            }
        });
    });

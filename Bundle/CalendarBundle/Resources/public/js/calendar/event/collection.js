/* global define */
define(['backbone', 'routing', 'oro/calendar/event/model'],
function(Backbone, routing, EventModel) {
    'use strict';

    /**
     * @export  oro/calendar/event/collection
     * @class   oro.calendar.event.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        route: 'oro_api_get_calendarevents',
        url: null,
        model: EventModel,

        /**
         * Calendar id
         * @property {int}
         */
        calendar: null,

        /**
         * Determine whether events from connected calendars should be included or not
         * @property {bool}
         */
        subordinate: false,

        /**
         * Sets a range of calendar events this collection works with
         *
         * @param {string} start A date/time specifies the begin of a range. RFC 3339 string
         * @param {string} end   A date/time specifies the end of a range. RFC 3339 string
         */
        setRange: function (start, end) {
            this.url = routing.generate(
                this.route,
                {calendar: this.calendar, start: start, end: end, subordinate: this.subordinate}
            );
        },

        /**
         * Sets a calendar this collection works with
         *
         * @param {int} calendarId
         */
        setCalendar: function(calendarId) {
            this.calendar = calendarId;
        },

        /**
         * Gets a calendar this collection works with
         *
         * @return {int} The calendar id
         */
        getCalendar: function() {
            return this.calendar;
        }
    });
});

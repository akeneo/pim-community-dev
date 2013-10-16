/* global define */
define(['backbone', 'routing', 'oro/calendar/model'],
    function(Backbone, routing, CalendarModel) {
        'use strict';

        /**
         * @export  oro/calendar/collection
         * @class   oro.calendar.Collection
         * @extends Backbone.Collection
         */
        return Backbone.Collection.extend({
            route: 'oro_api_get_calendarevents',
            url: null,
            model: CalendarModel,
            calendar: null,
            subordinate: false,

            setRange: function (start, end) {
                this.url = routing.generate(
                    this.route,
                    {calendar: this.calendar, start: start, end: end, subordinate: this.subordinate}
                );
            },
            getCalendar: function() {
                return this.calendar;
            }
        });
    });

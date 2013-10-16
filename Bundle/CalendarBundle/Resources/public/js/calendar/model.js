/* global define */
define(['backbone', 'routing', 'oro/calendar/collection'],
    function(Backbone, routing) {
        'use strict';

        /**
         * @export  oro/calendar/model
         * @class   oro.calendar.Model
         * @extends Backbone.Model
         */
        return Backbone.Model.extend({
            route: 'oro_api_get_calendarevents',
            urlRoot: null,

            defaults: {
                id: null,
                title : null,
                start: null,
                end: null,
                allDay: false,
                reminder: false
            },

            initialize: function() {
                this.urlRoot = routing.generate(this.route);
            }
        });
    });

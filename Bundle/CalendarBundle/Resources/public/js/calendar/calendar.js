/* jshint devel:true*/
/* global define, require */
define(['jquery', 'underscore', 'backbone', 'oro/translator', 'oro/app', 'oro/messenger', 'oro/loading-mask',
    'oro/calendar/event-collection', 'oro/calendar/event-model', 'oro/calendar/event-view',
    'oro/calendar/connection-collection', 'oro/calendar/connection-view'],
    function($, _, Backbone, __, app, messenger, LoadingMask,
             CalendarEventCollection, CalendarEvent, CalendarEventView,
             CalendarConnectionCollection, CalendarConnectionView) {
        'use strict';

        /**
         * @export  oro/calendar
         * @class   oro.Calendar
         * @extends Backbone.View
         */
        return Backbone.View.extend({
            /** @property */
            eventsTemplate: _.template(
                '<div>' +
                    '<div class="calendar-container">' +
                        '<div class="calendar"></div>' +
                        '<div class="loading-mask"></div>' +
                    '</div>' +
                '</div>'
            ),

            /** @property {Object} */
            selectors: {
                connections:        '.calendar-connections',
                events:             '.calendar-events',
                calendar:           '.calendar',
                loadingMask:        '.loading-mask',
                loadingMaskContent: '.loading-content'
            },

            /* this property is used to prevent loading of events from a server when the calendar object is created */
            enableEventLoading: false,

            initialize: function() {
                // init event collection
                this.options.collection = this.options.collection || new CalendarEventCollection();
                this.options.collection.setCalendar(this.options.calendar);
                this.options.collection.subordinate = this.options.subordinate;
                // init connection collection
                this.options.connections = this.options.connections || new CalendarConnectionCollection();
                this.options.connections.setCalendar(this.options.calendar);
                // remove no longer used options
                delete this.options.calendar;
                delete this.options.subordinate;

                // create a view for event details
                this.eventView = new CalendarEventView({
                    collection: this.getCollection(),
                    eventFormValidationScriptUrl: this.options.eventFormValidationScriptUrl,
                    timezoneOffset: this.options.timezoneOffset
                });
                // remove no longer used options
                delete this.options.eventFormValidationScriptUrl;
                delete this.options.timezoneOffset;

                // init events container
                var eventsContainer = this.$el.find(this.selectors.events);
                if (eventsContainer.length === 0) {
                    throw new Error("Cannot find '" + this.selectors.events + "' element.");
                }
                eventsContainer.empty();
                eventsContainer.append($(this.eventsTemplate()));

                // init connections container
                var connectionsContainer = this.$el.find(this.selectors.connections);
                if (connectionsContainer.length === 0) {
                    throw new Error("Cannot find '" + this.selectors.connections + "' element.");
                }
                connectionsContainer.empty();
                var connectionsTemplate = _.template($("#template-calendar-connections").html());
                connectionsContainer.append($(connectionsTemplate()));

                // create a view for a list of connections
                this.connectionsView = new CalendarConnectionView({
                    el: connectionsContainer,
                    collection: this.getConnections()
                });

                // init a loading mask control
                this.loadingMask = new LoadingMask();
                this.$el.find(this.selectors.loadingMask).append(this.loadingMask.render().$el);

                // subscribe to event collection events
                this.listenTo(this.getCollection(), 'add', this.onModelAdded);
                this.listenTo(this.getCollection(), 'change', this.onModelChanged);
                this.listenTo(this.getCollection(), 'destroy', this.onModelDeleted);
                // subscribe to connection collection events
                this.listenTo(this.getConnections(), 'add', this.onConnectionAddedOrDeleted);
                this.listenTo(this.getConnections(), 'change', this.onConnectionChanged);
                this.listenTo(this.getConnections(), 'destroy', this.onConnectionAddedOrDeleted);

                // prepare options for jQuery FullCalendar control
                var options = {
                        header: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'month,agendaWeek,agendaDay',
                            ignoreTimezone: false,
                            allDayDefault: false
                        },
                        selectHelper: true,
                        events: _.bind(this.loadEvents, this),
                        select: _.bind(this.select, this),
                        eventClick: _.bind(this.eventClick, this),
                        eventDrop: _.bind(this.eventDropOrResize, this),
                        eventResize: _.bind(this.eventDropOrResize, this),
                        loading: _.bind(this.showLoadingMask, this)
                    };
                var keys = ['date', 'defaultView', 'editable', 'selectable',
                    'titleFormat', 'columnFormat', 'timeFormat',
                    'firstDay', 'monthNames', 'monthNamesShort', 'dayNames', 'dayNamesShort'];
                _.extend(options, _.pick(this.options, keys));
                _.each(keys, _.bind(function (key) { delete this.options[key]; }, this));
                if (!_.isUndefined(options.date)) {
                    if (_.isString(options.date)) {
                        options.date = $.fullCalendar.parseISO8601(options.date, true);
                    }
                    options.year = options.date.getFullYear();
                    options.month = options.date.getMonth();
                    options.date = options.date.getDate();
                }

                // create jQuery FullCalendar control
                this.getCalendarElement().fullCalendar(options);
                this.enableEventLoading = true;
            },
            getCollection: function() {
                return this.options.collection;
            },
            getConnections: function() {
                return this.options.connections;
            },
            getCalendarElement: function() {
                return this.$el.find(this.selectors.calendar);
            },
            onModelAdded: function(event){
                var fcEvent = event.toJSON();
                this.prepareViewModel(fcEvent);
                this.getCalendarElement().fullCalendar('renderEvent', fcEvent);
            },
            onModelChanged: function(event){
                var fcEvent = this.getCalendarElement().fullCalendar('clientEvents', event.get('id'))[0];
                // copy all fields, except id, from event to fcEvent
                fcEvent = _.extend(fcEvent, _.pick(event.attributes, _.keys(_.omit(fcEvent, ['id']))));
                fcEvent.start = this.convertToViewDateTime(fcEvent.start);
                fcEvent.end = this.convertToViewDateTime(fcEvent.end);
                this.getCalendarElement().fullCalendar('updateEvent', fcEvent);
            },
            onModelDeleted: function(event) {
                this.getCalendarElement().fullCalendar('removeEvents', event.id);
            },
            onConnectionAddedOrDeleted: function () {
                this.getCalendarElement().fullCalendar('refetchEvents');
            },
            onConnectionChanged: function () {

            },
            select: function(start, end) {
                if (!this.eventView.model) {
                    try {
                        this.eventView.model = new CalendarEvent({
                            start: this.formatDateTimeForModel(start),
                            end: this.formatDateTimeForModel(end)
                        });
                        this.eventView.render();
                    } catch (err) {
                        this.showError(err);
                    }
                }
            },
            eventClick: function(fcEvent) {
                if (!this.eventView.model) {
                    try {
                        this.eventView.model = this.getCollection().get(fcEvent.id);
                        this.eventView.render();
                    } catch (err) {
                        this.showError(err);
                    }
                }
            },
            eventDropOrResize: function(fcEvent) {
                this.showSavingMask(true);
                try {
                    this.getCollection()
                        .get(fcEvent.id)
                        .save(
                            {
                                start: this.formatDateTimeForModel(fcEvent.start),
                                end: this.formatDateTimeForModel(!_.isNull(fcEvent.end) ? fcEvent.end : fcEvent.start)
                            },
                            {
                                success: _.bind(function () {
                                    this.showSavingMask(false);
                                }, this),
                                error: _.bind(function (model, response) {
                                    this.showSavingMask(false);
                                    this.showSaveEventError(response.responseJSON);
                                }, this)
                            });
                } catch (err) {
                    this.showSavingMask(false);
                    this.showLoadEventsError(err);
                }
            },
            loadEvents: function(start, end, callback) {
                try {
                    this.getCollection().setRange(
                        this.formatDateTimeForModel(start),
                        this.formatDateTimeForModel(end)
                    );
                    if (this.enableEventLoading) {
                        this.getCollection().fetch({
                            success: _.bind(function() {
                                var fcEvents = this.getCollection().toJSON();
                                this.prepareViewModels(fcEvents);
                                callback(fcEvents);
                            }, this),
                            error: _.bind(function(collection, response) {
                                this.getCalendarElement().fullCalendar('removeEvents', event.id);
                                this.showLoadEventsError(response.responseJSON);
                            }, this)
                        });
                    } else {
                        var fcEvents = this.getCollection().toJSON();
                        this.prepareViewModels(fcEvents);
                        callback(fcEvents);
                    }
                } catch (err) {
                    callback({});
                    this.showLoadEventsError(err);
                }
            },
            prepareViewModels : function (fcEvents) {
                _.each(fcEvents, _.bind(function (fcEvent) {
                    this.prepareViewModel(fcEvent);
                }, this));
            },
            prepareViewModel : function (fcEvent) {
                fcEvent.start = this.convertToViewDateTime(fcEvent.start);
                fcEvent.end = this.convertToViewDateTime(fcEvent.end);
                var colors = this.connectionsView.getCalendarColors(fcEvent.calendar);
                fcEvent.textColor = colors.color;
                fcEvent.color = colors.backgroundColor;
            },
            showSavingMask: function(show) {
                this._showMask(show, 'Saving...');
            },
            showLoadingMask: function(show) {
                this._showMask(show, 'Loading...');
            },
            _showMask: function(show, message) {
                if (this.enableEventLoading) {
                    if (show) {
                        this.loadingMask.$el
                            .find(this.selectors.loadingMaskContent)
                            .text(__(message));
                        this.loadingMask.show();
                    } else {
                        this.loadingMask.hide();
                    }
                }
            },
            showLoadEventsError: function (err) {
                this._showError(err, 'Sorry, calendar events were not loaded correctly');
            },
            showSaveEventError: function (err) {
                this._showError(err, 'Sorry, calendar event was not saved correctly');
            },
            showError: function (err) {
                this._showError(err, 'Sorry, unexpected error was occurred');
            },
            _showError: function (err, message) {
                if (!_.isUndefined(console)) {
                    console.error(_.isUndefined(err.stack) ? err : err.stack);
                }
                var msg = __(message);
                if (app.debug) {
                    if (!_.isUndefined(err.message)) {
                        msg += ': ' + err.message;
                    } else if (!_.isUndefined(err.errors) && _.isArray(err.errors)) {
                        msg += ': ' + err.errors.join();
                    } else if (_.isString(err)) {
                        msg += ': ' + err;
                    }
                }
                messenger.notificationFlashMessage('error', msg);
            },
            convertToViewDateTime: function (s) {
                return this.eventView.convertToViewDateTime(s);
            },
            formatDateTimeForModel: function (d) {
                return this.eventView.formatDateTimeForModel(d);
            }
        });
    });

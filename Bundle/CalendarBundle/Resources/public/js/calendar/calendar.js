/* jshint devel:true*/
/* global define, require */
define(['jquery', 'underscore', 'backbone', 'oro/translator', 'oro/app', 'oro/navigation', 'oro/loading-mask',
    'oro/calendar/collection', 'oro/calendar/model', 'oro/calendar/view'],
    function($, _, Backbone, __, app, Navigation, LoadingMask,
             CalendarEventCollection, CalendarEvent, CalendarEventView) {
        'use strict';

        /**
         * @export  oro/calendar/view
         * @class   oro.CalendarView
         * @extends Backbone.View
         */
        return Backbone.View.extend({
            /** @property */
            template: _.template(
                '<div class="container-fluid">' +
                    '<div class="calendar-container">' +
                        '<div class="calendar"></div>' +
                        '<div class="loading-mask"></div>' +
                    '</div>' +
                '</div>'
            ),

            /** @property {Object} */
            selectors: {
                calendar:           '.calendar',
                loadingMask:        '.loading-mask',
                loadingMaskContent: '.loading-content'
            },

            /* this property is used to prevent loading of events from a server when the calendar object is created */
            enableEventLoading: false,

            initialize: function() {
                this.options.collection = this.options.collection || new CalendarEventCollection();
                this.options.collection.calendar = this.options.calendar;
                delete this.options.calendar;
                this.options.collection.subordinate = this.options.subordinate;
                delete this.options.subordinate;

                this.eventView = new CalendarEventView({
                    collection: this.getCollection(),
                    eventFormValidationScriptUrl: this.options.eventFormValidationScriptUrl,
                    timezoneOffset: this.options.timezoneOffset
                });
                delete this.options.eventFormValidationScriptUrl;
                delete this.options.timezoneOffset;

                this.$el.empty();
                this.$el = this.$el.append($(this.template()));

                this.loadingMask = new LoadingMask();
                this.$el.find(this.selectors.loadingMask).append(this.loadingMask.render().$el);

                this.listenTo(this.getCollection(), 'add', this.addModel);
                this.listenTo(this.getCollection(), 'change', this.changeModel);
                this.listenTo(this.getCollection(), 'destroy', this.destroyModel);

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

                this.getCalendarElement().fullCalendar(options);
                this.enableEventLoading = true;
            },
            getCollection: function() {
                return this.options.collection;
            },
            getCalendarElement: function() {
                return this.$el.find(this.selectors.calendar);
            },
            addModel: function(event){
                var fcEvent = event.toJSON();
                fcEvent.start = this.convertToViewDateTime(fcEvent.start);
                fcEvent.end = this.convertToViewDateTime(fcEvent.end);
                this.getCalendarElement().fullCalendar('renderEvent', fcEvent);
            },
            changeModel: function(event){
                var fcEvent = this.getCalendarElement().fullCalendar('clientEvents', event.get('id'))[0];
                // copy all fields, except id, from event to fcEvent
                fcEvent = _.extend(fcEvent, _.pick(event.attributes, _.keys(_.omit(fcEvent, ['id']))));
                fcEvent.start = this.convertToViewDateTime(fcEvent.start);
                fcEvent.end = this.convertToViewDateTime(fcEvent.end);
                this.getCalendarElement().fullCalendar('updateEvent', fcEvent);
            },
            destroyModel: function(event) {
                this.getCalendarElement().fullCalendar('removeEvents', event.id);
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
                if (this.enableEventLoading) {
                    try {
                        this.getCollection().setRange(
                            this.formatDateTimeForModel(start),
                            this.formatDateTimeForModel(end)
                        );
                        this.getCollection().fetch({
                            success: _.bind(function() {
                                var events = this.getCollection().toJSON();
                                _.each(events, _.bind(function (event) {
                                    event.start = this.convertToViewDateTime(event.start);
                                    event.end = this.convertToViewDateTime(event.end);
                                }, this));
                                callback(events);
                            }, this),
                            error: _.bind(function(collection, response) {
                                callback({});
                                this.showLoadEventsError(response.responseJSON);
                            }, this)
                        });
                    } catch (err) {
                        callback({});
                        this.showLoadEventsError(err);
                    }
                } else {
                    var events = this.getCollection().toJSON();
                    _.each(events, _.bind(function (event) {
                        event.start = this.convertToViewDateTime(event.start);
                        event.end = this.convertToViewDateTime(event.end);
                    }, this));
                    callback(events);
                }
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
                var navigation = Navigation.getInstance();
                if (navigation) {
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
                    navigation.showMessage(msg);
                }
            },
            convertToViewDateTime: function (s) {
                return this.eventView.convertToViewDateTime(s);
            },
            formatDateTimeForModel: function (d) {
                return this.eventView.formatDateTimeForModel(d);
            }
        });
    });

/* jshint devel:true*/
/* global define */
define(['underscore', 'backbone', 'oro/translator', 'oro/app', 'oro/messenger', 'oro/loading-mask',
    'oro/calendar/event/collection', 'oro/calendar/event/model', 'oro/calendar/event/view',
    'oro/calendar/connection/collection', 'oro/calendar/connection/view', 'oro/formatter/datetime',
    'jquery.fullcalendar'],
function(_, Backbone, __, app, messenger, LoadingMask,
         EventCollection, EventModel, EventView,
         ConnectionCollection, ConnectionView, dateTimeFormatter) {
    'use strict';

    var $ = Backbone.$;

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
            calendar:           '.calendar',
            loadingMask:        '.loading-mask',
            loadingMaskContent: '.loading-content'
        },

        options: {
            eventsOptions: {
                editable: true,
                removable: true,
                collection: null,
                itemFormTemplateSelector: null,
                itemFormDeleteButtonSelector: null,
                calendar: null
            },
            connectionsOptions: {
                collection: null,
                containerTemplateSelector: null
            }
        },

        /**
         * this property is used to prevent loading of events from a server when the calendar object is created
         *  @property {bool}
         */
        enableEventLoading: false,
        fullCalendar: null,
        eventView: null,
        loadingMask: null,

        initialize: function() {
            // init event collection
            this.options.collection = this.options.collection || new EventCollection();
            this.options.collection.setCalendar(this.options.calendar);
            this.options.collection.subordinate = this.options.eventsOptions.subordinate;

            // set options for new events
            this.options.newEventEditable = this.options.eventsOptions.editable;
            this.options.newEventRemovable = this.options.eventsOptions.removable;

            // subscribe to event collection events
            this.listenTo(this.getCollection(), 'add', this.onEventAdded);
            this.listenTo(this.getCollection(), 'change', this.onEventChanged);
            this.listenTo(this.getCollection(), 'destroy', this.onEventDeleted);
        },

        getEventsView: function (model) {
            if (!this.eventView) {
                // create a view for event details
                this.eventView = new EventView({
                    model: model,
                    calendar: this.options.calendar,
                    formTemplateSelector: this.options.eventsOptions.itemFormTemplateSelector
                });
                // subscribe to event view collection events
                this.listenTo(this.eventView, 'addEvent', this.handleEventViewAdd);
                this.listenTo(this.eventView, 'remove', this.handleEventViewRemove);
            }
            return this.eventView;
        },

        handleEventViewRemove: function() {
            this.eventView = null;
        },

        /**
         * Init and get a loading mask control
         *
         * @returns {Element}
         */
        getLoadingMask: function () {
            if (!this.loadingMask) {
                this.loadingMask = new LoadingMask();
                this.$el.find(this.selectors.loadingMask).append(this.loadingMask.render().$el);
            }
            return this.loadingMask;
        },

        getCollection: function() {
            return this.options.collection;
        },

        getCalendarElement: function() {
            if (!this.fullCalendar) {
                this.fullCalendar = this.$el.find(this.selectors.calendar);
            }
            return this.fullCalendar;
        },

        handleEventViewAdd: function(eventModel) {
            this.getCollection().add(eventModel);
        },

        onEventAdded: function(eventModel){
            var fcEvent = eventModel.toJSON();
            this.prepareViewModel(fcEvent);

            this.getCalendarElement().fullCalendar('renderEvent', fcEvent);
        },

        onEventChanged: function(eventModel){
            var fcEvent = this.getCalendarElement().fullCalendar('clientEvents', eventModel.get('id'))[0];
            // copy all fields, except id, from event to fcEvent
            fcEvent = _.extend(fcEvent, _.pick(eventModel.attributes, _.keys(_.omit(fcEvent, ['id']))));
            this.prepareViewModel(fcEvent);
            this.getCalendarElement().fullCalendar('updateEvent', fcEvent);
        },

        onEventDeleted: function(eventModel) {
            this.getCalendarElement().fullCalendar('removeEvents', eventModel.id);
        },

        onConnectionAddedOrDeleted: function () {
            this.getCalendarElement().fullCalendar('refetchEvents');
        },

        onConnectionChanged: function () {

        },

        select: function(start, end) {
            if (!this.eventView) {
                try {
                    // TODO: All date values must be in UTC representation according to config timezone,
                    // https://magecore.atlassian.net/browse/BAP-2203
                    var eventModel = new EventModel({
                        start: this.formatDateTimeForModel(start),
                        end: this.formatDateTimeForModel(end),
                        editable: this.options.newEventEditable,
                        removable: this.options.newEventRemovable
                    });
                    this.getEventsView(eventModel).render();
                } catch (err) {
                    this.showError(err);
                }
            }
        },

        eventClick: function(fcEvent) {
            if (!this.eventView) {
                try {
                    var eventModel = this.getCollection().get(fcEvent.id);
                    this.getEventsView(eventModel).render();
                } catch (err) {
                    this.showError(err);
                }
            }
        },

        eventDropOrResize: function(fcEvent) {
            this.showSavingMask();
            try {
                this.getCollection()
                    .get(fcEvent.id)
                    .save(
                        {
                            start: this.formatDateTimeForModel(fcEvent.start),
                            end: this.formatDateTimeForModel(!_.isNull(fcEvent.end) ? fcEvent.end : fcEvent.start)
                        },
                        {
                            success: _.bind(this._hideMask, this),
                            error: _.bind(function (model, response) {
                                this.showSaveEventError(response.responseJSON);
                            }, this)
                        });
            } catch (err) {
                this.showLoadEventsError(err);
            }
        },

        loadEvents: function(start, end, callback) {
            var onEventsLoad = _.bind(function() {
                var fcEvents = this.getCollection().toJSON();
                this.prepareViewModels(fcEvents);
                this._hideMask();
                callback(fcEvents);
            }, this);

            try {
                this.getCollection().setRange(
                    this.formatDateTimeForModel(start),
                    this.formatDateTimeForModel(end)
                );
                if (this.enableEventLoading) {
                    // load events from a server
                    this.getCollection().fetch({
                        success: onEventsLoad,
                        error: _.bind(function(collection, response) {
                            callback({});
                            this.showLoadEventsError(response.responseJSON);
                        }, this)
                    });
                } else {
                    // use already loaded events
                    onEventsLoad();
                }
            } catch (err) {
                callback({});
                this.showLoadEventsError(err);
            }
        },

        prepareViewModels : function (fcEvents) {
            _.each(fcEvents, this.prepareViewModel, this);
        },

        prepareViewModel : function (fcEvent) {
            // convert start and end dates from backend formatted string to Date object
            fcEvent.start = dateTimeFormatter.unformatBackendDateTime(fcEvent.start);
            fcEvent.end = dateTimeFormatter.unformatBackendDateTime(fcEvent.end);
            // set an event text and background colors the same as the owning calendar
            var colors = this.connectionsView.getCalendarColors(fcEvent.calendar);
            fcEvent.textColor = colors.color;
            fcEvent.color = colors.backgroundColor;
        },

        formatDateTimeForModel: function (date) {
            return dateTimeFormatter.convertDateTimeToBackendFormat(date);
        },

        showSavingMask: function() {
            this._showMask(__('Saving...'));
        },

        showLoadingMask: function() {
            this._showMask(__('Loading...'));
        },

        _showMask: function(message) {
            if (this.enableEventLoading) {
                var loadingMaskInstance = this.getLoadingMask();
                loadingMaskInstance.$el
                    .find(this.selectors.loadingMaskContent)
                    .text(message);
                loadingMaskInstance.show();
            }
        },

        _hideMask: function() {
            if (this.loadingMask) {
                this.loadingMask.hide();
            }
        },

        showLoadEventsError: function (err) {
            this._showError(err, __('Sorry, calendar events were not loaded correctly'));
        },

        showSaveEventError: function (err) {
            this._showError(err, __('Sorry, calendar event was not saved correctly'));
        },

        showError: function (err) {
            this._showError(err, __('Sorry, unexpected error was occurred'));
        },

        _showError: function (err, message) {
            this._hideMask();
            if (!_.isUndefined(console)) {
                console.error(_.isUndefined(err.stack) ? err : err.stack);
            }
            var msg = message;
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

        initCalendarContainer: function() {
            // init events container
            var eventsContainer = this.$el.find(this.options.eventsOptions.containerSelector);
            if (eventsContainer.length === 0) {
                throw new Error("Cannot find '" + this.options.eventsOptions.containerSelector + "' element.");
            }
            eventsContainer.empty();
            eventsContainer.append($(this.eventsTemplate()));
        },

        initializeFullCalendar: function () {
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
                loading: _.bind(function(show) {
                    if (show) {
                        this.showLoadingMask();
                    } else {
                        this._hideMask();
                    }
                }, this),
                allDayText: __('all-day'),
                buttonText: {
                    today: __('today'),
                    month: __('month'),
                    week: __('week'),
                    day: __('day')
                }
            };
            var keys = ['date', 'defaultView', 'editable', 'selectable',
                'titleFormat', 'columnFormat', 'timeFormat', 'axisFormat',
                'firstDay', 'monthNames', 'monthNamesShort', 'dayNames', 'dayNamesShort'];
            _.extend(options, _.pick(this.options.eventsOptions, keys));
            if (!_.isUndefined(options.date)) {
                if (_.isString(options.date)) {
                    options.date = $.fullCalendar.parseISO8601(options.date, true);
                }
                options.year = options.date.getFullYear();
                options.month = options.date.getMonth();
                options.date = options.date.getDate();
            }

            //Fix aspect ration to prevent double scroll for week and day views.
            options.viewRender = _.bind(function(view) {
                if (view.name !== 'month') {
                    this.getCalendarElement().fullCalendar('option', 'aspectRatio', 1.0);
                } else {
                    this.getCalendarElement().fullCalendar('option', 'aspectRatio', 1.35);
                }
            }, this);
            // create jQuery FullCalendar control
            this.getCalendarElement().fullCalendar(options);
            this.enableEventLoading = true;
        },

        initializeConnectionsView: function () {
            // init connections container
            var connectionsContainer = this.$el.find(this.options.connectionsOptions.containerSelector);
            if (connectionsContainer.length === 0) {
                throw new Error("Cannot find '" + this.options.connectionsOptions.containerSelector + "' element.");
            }
            connectionsContainer.empty();
            var connectionsTemplate = _.template($(this.options.connectionsOptions.containerTemplateSelector).html());
            connectionsContainer.append($(connectionsTemplate()));

            // create a view for a list of connections
            this.connectionsView = new ConnectionView({
                el: connectionsContainer,
                collection: this.options.connectionsOptions.collection,
                calendar: this.options.calendar,
                itemTemplateSelector: this.options.connectionsOptions.itemTemplateSelector
            });

            this.listenTo(this.connectionsView, 'connectionAdd', this.onConnectionAddedOrDeleted);
            this.listenTo(this.connectionsView, 'connectionChange', this.onConnectionChanged);
            this.listenTo(this.connectionsView, 'connectionRemove', this.onConnectionAddedOrDeleted);
        },

        render: function() {
            // init views
            this.initCalendarContainer();
            this.initializeConnectionsView();
            // initialize jQuery FullCalendar control
            this.initializeFullCalendar();

            return this;
        }
    });
});

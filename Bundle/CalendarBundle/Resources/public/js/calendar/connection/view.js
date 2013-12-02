/* global define */
define(['jquery', 'underscore', 'backbone', 'oro/translator', 'oro/app', 'oro/messenger',
    'oro/calendar/connection/collection', 'oro/calendar/connection/model'],
function($, _, Backbone, __, app, messenger, ConnectionCollection, ConnectionModel) {
    'use strict';

    /**
     * @export  oro/calendar/connection/view
     * @class   oro.calendar.connection.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /**
         * A list of text/background colors are used to determine colors of events of connected calendars
         *  @property {Array}
         */
        colors: [
            ['FFFFFF', 'AC725E'], ['FFFFFF', 'D06B64'], ['FFFFFF', 'F83A22'], ['000000', 'FA573C'],
            ['000000', 'FF7537'], ['000000', 'FFAD46'], ['000000', '42D692'], ['FFFFFF', '16A765'],
            ['000000', '7BD148'], ['000000', 'B3DC6C'], ['000000', 'FBE983'], ['000000', 'FAD165'],
            ['000000', '92E1C0'], ['000000', '9FE1E7'], ['000000', '9FC6E7'], ['FFFFFF', '4986E7'],
            ['000000', '9A9CFF'], ['000000', 'B99AFF'], ['000000', 'C2C2C2'], ['000000', 'CABDBF'],
            ['000000', 'CCA6AC'], ['000000', 'F691B2'], ['FFFFFF', 'CD74E6'], ['FFFFFF', 'A47AE2']
        ],

        /** @property {Object} */
        attrs: {
            calendar:        'data-calendar',
            owner:           'data-owner',
            color:           'data-color',
            backgroundColor: 'data-bg-color'
        },

        /** @property {Object} */
        selectors: {
            container:     '.calendars',
            itemContainer: '.connection-container',
            item:          '.connection-item',
            lastItem:      '.connection-item:last',
            findItemByCalendar: function (calendarId) { return '.connection-item[data-calendar="' + calendarId + '"]'; },
            findItemByOwner: function (ownerId) { return '.connection-item[data-owner="' + ownerId + '"]'; },
            removeButton:  '.remove-connection-button',
            newOwnerSelector: '#new_calendar_owner'
        },

        /** @property {Object} */
        calendarColorCache: null,

        initialize: function() {
            this.options.collection = this.options.collection || new ConnectionCollection();
            this.options.collection.setCalendar(this.options.calendar);
            this.template = _.template($(this.options.itemTemplateSelector).html());

            this.defaultColors = this.findColors('4986E7');

            // render connected calendars
            this.getCollection().each(_.bind(function (model) {
                this.onModelAdded(model);
            }, this));

            // subscribe to connection collection events
            this.listenTo(this.getCollection(), 'add', this.onModelAdded);
            this.listenTo(this.getCollection(), 'change', this.onModelChanged);
            this.listenTo(this.getCollection(), 'destroy', this.onModelDeleted);

            // subscribe to connect new calendar event
            var container = this.$el.closest(this.selectors.container);
            container.find(this.selectors.newOwnerSelector).on('change', _.bind(function (e) {
                this.addModel(e.val);
                // clear autocomplete
                $(e.target).select2('val', '');
            }, this));
        },

        getCollection: function() {
            return this.options.collection;
        },

        onModelAdded: function(model){
            var viewModel = model.toJSON();
            // init text/background colors
            if (_.isEmpty(viewModel.color) && _.isEmpty(viewModel.backgroundColor)) {
                var colors = this.findNextColors(this.$el.find(this.selectors.lastItem).attr(this.attrs.backgroundColor));
                viewModel.color = colors[0];
                viewModel.backgroundColor = colors[1];
            } else if (_.isEmpty(viewModel.color)) {
                viewModel.color = this.defaultColors[0];
            } else if (_.isEmpty(viewModel.backgroundColor)) {
                viewModel.backgroundColor = this.defaultColors[1];
            }

            var el = $(this.template(viewModel));
            // set 'data-' attributes
            _.each(this.attrs, function (value, key) {
                el.attr(value, viewModel[key]);
            });
            // subscribe to disconnect calendar event
            el.on('click', this.selectors.removeButton, _.bind(function (e) {
                this.deleteModel($(e.currentTarget).closest(this.selectors.item).attr(this.attrs.calendar));
            }, this));

            this.$el.find(this.selectors.itemContainer).append(el);

            this.trigger('connectionAdd', model);
        },

        onModelChanged: function(model){
            this.setCalendarColorCache(model.get('calendar'), model.get('color'), model.get('backgroundColor'));
            this.trigger('connectionChange', model);
        },

        onModelDeleted: function(model) {
            this.resetCalendarColorCache(model.get('calendar'));
            this.$el.find(this.selectors.findItemByCalendar(model.get('calendar'))).remove();
            this.trigger('connectionRemove', model);
        },

        addModel: function (ownerId) {
            var el = this.$el.find(this.selectors.findItemByOwner(ownerId));
            if (el.length > 0) {
                messenger.notificationFlashMessage('warning', __('This calendar already exists.'));
            } else {
                var savingMsg = messenger.notificationMessage('warning', __('Adding the calendar, please wait ...'));
                try {
                    var model = new ConnectionModel();
                    model.set('owner', ownerId);
                    this.getCollection().create(model, {
                        wait: true,
                        success: _.bind(function () {
                            savingMsg.close();
                            messenger.notificationFlashMessage('success', __('The calendar was added.'));
                        }, this),
                        error: _.bind(function (collection, response) {
                            savingMsg.close();
                            this.showAddError(response.responseJSON);
                        }, this)
                    });
                } catch (err) {
                    savingMsg.close();
                    this.showError(err);
                }
            }
        },

        deleteModel: function (calendarId) {
            var deletingMsg = messenger.notificationMessage('warning', __('Excluding the calendar, please wait ...'));
            try {
                var model = this.getCollection().get(calendarId);
                model.destroy({
                    wait: true,
                    success: _.bind(function () {
                        deletingMsg.close();
                        messenger.notificationFlashMessage('success', __('The calendar was excluded.'));
                    }, this),
                    error: _.bind(function (model, response) {
                        deletingMsg.close();
                        this.showDeleteError(response.responseJSON);
                    }, this)
                });
            } catch (err) {
                deletingMsg.close();
                this.showError(err);
            }
        },

        getCalendarColors: function (calendarId) {
            if (!_.isNull(this.calendarColorCache) && this.calendarColorCache.calendarId == calendarId) {
                return this.calendarColorCache.colors;
            }
            var el = this.$el.find(this.selectors.findItemByCalendar(calendarId));
            this.setCalendarColorCache(calendarId, el.attr(this.attrs.color), el.attr(this.attrs.backgroundColor));
            return this.calendarColorCache.colors;
        },

        setCalendarColorCache: function (calendarId, color, backgroundColor) {
            this.calendarColorCache = {
                calendarId: calendarId,
                colors: {
                    color: '#' + color,
                    backgroundColor: '#' + backgroundColor
                }};
        },

        resetCalendarColorCache: function (calendarId) {
            if (!_.isNull(this.calendarColorCache) && this.calendarColorCache.calendarId == calendarId) {
                this.calendarColorCache = null;
            }
        },

        findColors: function (bgColor) {
            if (_.isEmpty(bgColor)) {
                return this.findColors(this.defaultColors[1]);
            }
            bgColor = bgColor.toUpperCase();
            var result = _.find(this.colors, function(item) { return item[1] === bgColor; });
            if (_.isUndefined(result)) {
                result = this.findColors(this.defaultColors[1]);
            }
            return result;
        },

        findNextColors: function (bgColor) {
            if (_.isEmpty(bgColor)) {
                return this.findColors(this.defaultColors[1]);
            }
            bgColor = bgColor.toUpperCase();
            var i = -1;
            _.each(this.colors, function(item, index) {
                if (item[1] === bgColor) {
                    i = index;
                }
            });
            if (i === -1) {
                return this.findColors(this.defaultColors[1]);
            }
            if ((i + 1) === _.size(this.colors)) {
                return _.first(this.colors);
            }
            return this.colors[i + 1];
        },

        showAddError: function (err) {
            this._showError(err, __('Sorry, the calendar adding was failed'));
        },

        showDeleteError: function (err) {
            this._showError(err, __('Sorry, the calendar excluding was failed'));
        },

        showError: function (err) {
            this._showError(err, __('Sorry, unexpected error was occurred'));
        },

        _showError: function (err, message) {
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
        }
    });
});

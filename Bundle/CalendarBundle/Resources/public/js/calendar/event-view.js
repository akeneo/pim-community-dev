/* global define */
define(['jquery', 'underscore', 'backbone', 'oro/translator', 'oro/dialog-widget', 'oro/layout',
    'oro/loading-mask', 'oro/form-validation', 'oro/delete-confirmation'],
    function($, _, Backbone, __, DialogWidget, layout, LoadingMask, FormValidation, DeleteConfirmation) {
        'use strict';

        /**
         * @export  oro/calendar/event-view
         * @class   oro.CalendarEventView
         * @extends Backbone.View
         */
        return Backbone.View.extend({
            /** @property {Object} */
            selectors: {
                loadingMaskContent: '.loading-content'
            },

            initialize: function() {
                var templateHtml = $("#template-calendar-event").html();
                templateHtml = templateHtml
                    .replace(/\{\s*(\w+)\s*\}/g, '<%- $1 %>')
                    .replace(/checkedif="(\w+)"/g, '<% if ($1) { %> checked="checked"<% } %>');
                this.template = _.template(templateHtml);
                var dateMatch = templateHtml.match(/data-dateformat="([^"]*)"/);
                this.options.dateformat = dateMatch[1];
                var timeMatch = templateHtml.match(/data-timeformat="([^"]*)"/);
                this.options.timeformat = timeMatch[1];
            },

            getCollection: function() {
                return this.options.collection;
            },

            render: function() {
                // prepare dialog content
                var title = this.model.isNew() ? __('Add New Event') : __('Edit Event');
                var data = this.model.toJSON();
                // convert start and end dates from RFC 3339 string to jQuery date/time string
                data.start = this.convertDateTimeFromModelToDatepicker(data.start);
                data.end = this.convertDateTimeFromModelToDatepicker(data.end);
                var el = this.template(data);
                // create a dialog
                this.eventDialog = new DialogWidget({
                    el: el,
                    title: title,
                    stateEnabled: false,
                    incrementalPosition: false,
                    loadingMaskEnabled: false,
                    dialogOptions: {
                        modal: true,
                        resizable: false,
                        width: 475,
                        autoResize: true,
                        close: _.bind(function() {
                            delete this.model;
                            this.loadingMask.remove();
                            this.loadingMask = null;
                        }, this)
                    }
                });

                // init controls and show the dialog
                layout.init(this.eventDialog.$el);
                this.eventDialog.render();

                // override form submit behavior
                var form = this.eventDialog.$el.find('input:submit').closest('form');
                form.off('submit');
                form.on('submit', _.bind(function (e) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        this.saveModel();
                        return false;
                    }, this));

                // subscribe to 'delete event' event
                this.eventDialog.$el.closest('.ui-dialog').find('#btn-remove-calendarevent')
                    .on('click', _.bind(function (e) {
                        var el = $(e.target);
                        var confirm = new DeleteConfirmation({
                            content: el.data('message')
                        });
                        confirm.on('ok', _.bind(function() {
                            this.deleteModel();
                        }, this));
                        confirm.open();
                        return false;
                    }, this));

                // init form validation script
                if (!_.isUndefined(this.options.eventFormValidationScriptUrl)
                    && !_.isEmpty(this.options.eventFormValidationScriptUrl)) {
                    $.getScript(this.options.eventFormValidationScriptUrl);
                }

                // init loading mask control
                this.loadingMask = new LoadingMask();
                this.eventDialog.$el.closest('.ui-dialog').append(this.loadingMask.render().$el);

                return this;
            },

            saveModel: function() {
                this.showSavingMask(true);
                try {
                    var data = this.getEventFormData();

                    if (this.model.isNew()) {
                        data.calendar = this.getCollection().getCalendar();
                        // set model fields
                        _.each(data, _.bind(function (value, key) {
                            this.model.set(key, value);
                        }, this));
                        this.getCollection().create(this.model, {
                            wait: true,
                            success: _.bind(function () {
                                this.showSavingMask(false);
                                this.eventDialog.remove();
                            }, this),
                            error: _.bind(function (collection, response) {
                                this.showSavingMask(false);
                                this.showError(response.responseJSON);
                            }, this)
                        });
                    } else {
                        this.model.save(data, {
                            wait: true,
                            success: _.bind(function () {
                                this.showSavingMask(false);
                                this.eventDialog.remove();
                            }, this),
                            error: _.bind(function (model, response) {
                                this.showSavingMask(false);
                                this.showError(response.responseJSON);
                            }, this)
                        });
                    }
                } catch (err) {
                    this.showSavingMask(false);
                    this.showError(err);
                }
            },

            deleteModel: function() {
                this.showDeletingMask(true);
                try {
                    this.model.destroy({
                        wait: true,
                        success: _.bind(function () {
                            this.showDeletingMask(false);
                            this.eventDialog.remove();
                        }, this),
                        error: _.bind(function (model, response) {
                            this.showDeletingMask(false);
                            this.showError(response.responseJSON);
                        }, this)
                    });
                } catch (err) {
                    this.showDeletingMask(false);
                    this.showError(err);
                }
            },

            showSavingMask: function(show) {
                this._showMask(show, 'Saving...');
            },

            showDeletingMask: function(show) {
                this._showMask(show, 'Deleting...');
            },

            _showMask: function(show, message) {
                if (this.loadingMask) {
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

            showError: function (err) {
                if (this.eventDialog) {
                    FormValidation.handleErrors(this.eventDialog.$el.parent(), err);
                }
            },

            getEventFormData: function () {
                var keys = ['title', 'start', 'end', 'allDay', 'reminder'];
                var data = {};
                var container = this.eventDialog.$el.parent();
                var eventFormFieldPrefix = FormValidation.getFormFieldPrefix(container);
                _.each(keys, function (key) {
                    var el = container.find('#' + eventFormFieldPrefix + key);
                    if (el.attr('type') == 'checkbox') {
                        data[key] = el.is(':checked');
                    } else {
                        data[key] = el.val();
                    }
                });

                // convert start and end dates from jQuery date/time string to RFC 3339 string
                data.start = this.convertDateTimeFromDatepickerToModel(data.start);
                data.end = this.convertDateTimeFromDatepickerToModel(data.end);

                return data;
            },

            convertDateTimeFromDatepickerToModel: function (s) {
                var d = $.datepicker.parseDateTime(
                    this.options.dateformat,
                    this.options.timeformat,
                    s,
                    {},
                    {separator: ' ', timeFormat: this.options.timeformat}
                );
                return this.formatDateTimeForModel(d);
            },

            convertDateTimeFromModelToDatepicker: function (s) {
                var d = this.convertToViewDateTime(s);
                var t = {hour: d.getHours(), minute: d.getMinutes(), second: d.getSeconds()};
                return $.datepicker.formatDate(this.options.dateformat, d) + ' ' +
                    $.datepicker.formatTime(this.options.timeformat, t);
            },

            formatDateTimeForModel: function (d) {
                if (_.isNull(d)) {
                    return '';
                }
                d = new Date(d.getTime() - this.options.timezoneOffset * 60000);
                return d.getFullYear() +
                    '-' + this.pad((d.getMonth() + 1)) +
                    '-' + this.pad(d.getDate()) +
                    'T' + this.pad(d.getHours()) +
                    ':' + this.pad(d.getMinutes()) +
                    ':' + this.pad(d.getSeconds()) +
                    '.000Z';
            },

            convertToViewDateTime: function (s) {
                var d = $.fullCalendar.parseISO8601(s, true);
                return new Date(d.getTime() + this.options.timezoneOffset * 60000);
            },

            pad: function (s) {
                s += '';
                if (s.length === 1) {
                    s = '0' + s;
                }
                return s;
            }
        });
    });

/* global define */
define(['underscore', 'backbone', 'oro/translator', 'oro/dialog-widget', 'oro/layout',
    'oro/loading-mask', 'oro/form-validation', 'oro/delete-confirmation', 'oro/formatter/datetime'],
function(_, Backbone, __, DialogWidget, layout, LoadingMask, FormValidation, DeleteConfirmation, dateTimeFormatter) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  oro/calendar/event/view
     * @class   oro.calendar.event.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /** @property {Object} */
        selectors: {
            loadingMaskContent: '.loading-content'
        },

        initialize: function() {
            var templateHtml = $(this.options.formTemplateSelector).html();
            templateHtml = templateHtml
                .replace(/\{\s*(\w+)\s*\}/g, '<%- $1 %>')
                .replace(/checkedif="(\w+)"/g, '<% if ($1) { %> checked="checked"<% } %>');
            this.template = _.template(templateHtml);
        },

        getCollection: function() {
            return this.options.collection;
        },

        render: function() {
            // prepare dialog content
            var title = this.model.isNew() ? __('Add New Event') : __('Edit Event');
            var data = this.model.toJSON();
            // convert start and end dates from RFC 3339 string to jQuery date/time string
            data.start = dateTimeFormatter.formatDateTime(data.start);
            data.end = dateTimeFormatter.formatDateTime(data.end);
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
            this.eventDialog.$el.closest('.ui-dialog').find(this.options.formDeleteButtonSelector)
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
            if (!_.isUndefined(this.options.formValidationScriptUrl)
                && !_.isEmpty(this.options.formValidationScriptUrl)) {
                $.getScript(this.options.formValidationScriptUrl);
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
            data.start = this.formatDateTimeForModel(data.start);
            data.end = this.formatDateTimeForModel(data.end);

            return data;
        },

        formatDateTimeForModel: function (d) {
            return dateTimeFormatter.unformatDateTime(d);
        }
    });
});

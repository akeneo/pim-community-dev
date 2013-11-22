/* global define */
define(['underscore', 'backbone', 'oro/translator', 'oro/dialog-widget','oro/loading-mask', 'oro/form-validation',
    'oro/delete-confirmation', 'oro/calendar/event/model'],
function(_, Backbone, __, DialogWidget, LoadingMask, FormValidation, DeleteConfirmation, EventModel) {
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

        options: {
            formTemplateSelector: null,
            calendar: null
        },

        initialize: function() {
            var templateHtml = $(this.options.formTemplateSelector).html();
            this.template = _.template(templateHtml);

            this.listenTo(this.model, 'sync', this.onModelSave);
            this.listenTo(this.model, 'destroy', this.onModelDelete);
        },

        remove: function() {
            this.trigger('remove');
            this._hideMask();
            Backbone.View.prototype.remove.apply(this, arguments);
        },

        onModelSave: function() {
            this.trigger('addEvent', this.model);
            this.eventDialog.remove();
            this.remove();
        },

        onModelDelete: function() {
            this.eventDialog.remove();
            this.remove();
        },

        render: function() {
            // create a dialog
            if (!this.model) {
                this.model = new EventModel();
            }
            var modelData = this.model.toJSON();
            var eventForm = this.template(modelData);
            eventForm = this.fillForm(eventForm, modelData);

            this.eventDialog = new DialogWidget({
                el: eventForm,
                title: this.model.isNew() ? __('Add New Event') : __('Edit Event'),
                stateEnabled: false,
                incrementalPosition: false,
                loadingMaskEnabled: false,
                dialogOptions: {
                    modal: true,
                    resizable: false,
                    width: 475,
                    autoResize: true,
                    close: _.bind(this.remove, this)
                },
                submitHandler: _.bind(function () {
                    this.saveModel();
                }, this)
            });
            this.eventDialog.render();

            // subscribe to 'delete event' event
            var onDelete = _.bind(function (e) {
                e.preventDefault();
                var el = $(e.target);
                var confirm = new DeleteConfirmation({
                    content: el.data('message')
                });
                confirm.on('ok', _.bind(this.deleteModel, this));
                confirm.open();
            }, this);
            this.eventDialog.getAction('delete', 'adopted', function(deleteAction) {
                deleteAction.on('click', onDelete);
            });

            // init loading mask control
            this.loadingMask = new LoadingMask();
            this.eventDialog.$el.closest('.ui-dialog').append(this.loadingMask.render().$el);

            return this;
        },

        saveModel: function() {
            this.showSavingMask();
            try {
                var data = this.getEventFormData();
                data.calendar = this.options.calendar;

                this.model.save(data, {
                    wait: true,
                    error: _.bind(this._handleResponseError, this)
                });
            } catch (err) {
                this.showError(err);
            }
        },

        deleteModel: function() {
            this.showDeletingMask();
            try {
                this.model.destroy({
                    wait: true,
                    error: _.bind(this._handleResponseError, this)
                });
            } catch (err) {
                this.showError(err);
            }
        },

        showSavingMask: function() {
            this._showMask(__('Saving...'));
        },

        showDeletingMask: function() {
            this._showMask(__('Deleting...'));
        },

        _showMask: function(message) {
            if (this.loadingMask) {
                this.loadingMask.$el
                    .find(this.selectors.loadingMaskContent)
                    .text(message);
                this.loadingMask.show();
            }
        },

        _hideMask: function() {
            if (this.loadingMask) {
                this.loadingMask.hide();
            }
        },

        _handleResponseError: function(model, response) {
            this.showError(response.responseJSON);
        },

        showError: function (err) {
            this._hideMask();
            if (this.eventDialog) {
                FormValidation.handleErrors(this.eventDialog.$el.parent(), err);
            }
        },

        fillForm: function(form, modelData) {
            form = $(form);
            _.each(modelData, function(value, key) {
                var input = form.find('[name$="[' + key + ']"]');
                if (input.length) {
                    if (input.is(':checkbox')) {
                        input.prop('checked', value);
                    } else {
                        input.val(value);
                    }
                    input.change();
                }
            });
            return form;
        },

        getEventFormData: function () {
            var fieldNameRegex = /\[(\w+)\]$/;
            var data = {};
            var formData = this.eventDialog.form.serializeArray();
            formData = formData.concat(this.eventDialog.form.find('input[type=checkbox]:not(:checked)')
                .map(function() {
                    return {"name": this.name, "value": false};
                }).get()
            );
            _.each(formData, function (dataItem) {
                var fieldNameData = fieldNameRegex.exec(dataItem.name);
                if (fieldNameData && fieldNameData.length == 2) {
                    data[fieldNameData[1]] = dataItem.value;
                }
            });

            return data;
        }
    });
});

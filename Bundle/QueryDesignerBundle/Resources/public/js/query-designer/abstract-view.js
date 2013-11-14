/* global define */
define(['underscore', 'backbone', 'oro/translator', 'oro/form-validation', 'oro/delete-confirmation',
    'jquery-outer-html'],
function(_, Backbone, __, FormValidation, DeleteConfirmation) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  oro/query-designer/abstract-view
     * @class   oro.queryDesigner.AbstractView
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /** @property {Object} */
        options: {
            collection: null,
            itemTemplateSelector: null,
            itemFormSelector: null,
            updateStorage: function (data) { }
        },

        /** @property {Object} */
        selectors: {
            itemContainer:  '.column-container',
            cancelButton:   '.cancel-button',
            saveButton:     '.save-button',
            addButton:      '.add-button',
            editButton:     '.edit-button',
            deleteButton:   '.delete-button'
        },

        /** @property */
        optionTemplate: _.template('<option value="<%- id %>"><%- text %></option>'),

        /** @property jQuery */
        form: null,

        /** @property jQuery */
        columnSelector: null,

        initialize: function() {
            this.options.collection = this.options.collection || new this.collectionClass();

            this.itemTemplate = _.template($(this.options.itemTemplateSelector).html());

            // subscribe to collection events
            this.listenTo(this.getCollection(), 'add', this.onModelAdded);
            this.listenTo(this.getCollection(), 'change', this.onModelChanged);
            this.listenTo(this.getCollection(), 'remove', this.onModelDeleted);
            this.listenTo(this.getCollection(), 'reset', this.onResetCollection);
        },

        render: function() {
            this.initForm();
            this.getContainer().empty();
            this.getCollection().each(_.bind(function (model) {
                this.onModelAdded(model);
            }, this));

            return this;
        },

        getCollection: function() {
            return this.options.collection;
        },

        getContainer: function() {
            return $(this.selectors.itemContainer);
        },

        getColumnSelector: function () {
            return this.columnSelector;
        },

        addModel: function(model) {
            model.set('id', _.uniqueId('column'));
            this.getCollection().add(model);
        },

        deleteModel: function(model) {
            this.getCollection().remove(model);
        },

        onModelAdded: function(model) {
            var data = model.toJSON();
            _.each(data, _.bind(function (value, key) {
                data[key] = this.getLocalizedText(key, value);
            }, this));
            var item = $(this.itemTemplate(data));
            this.bindItemActions(item);
            this.getContainer().append(item);
            this.updateStorage();
        },

        onModelChanged: function(model) {
            var data = model.toJSON();
            _.each(data, _.bind(function (value, key) {
                data[key] = this.getLocalizedText(key, value);
            }, this));
            var item = $(this.itemTemplate(data));
            this.bindItemActions(item);
            this.getContainer().find('[data-id="' + model.id + '"]').outerHTML(item);
            this.updateStorage();
        },

        onModelDeleted: function(model) {
            this.getContainer().find('[data-id="' + model.id + '"]').remove();
            this.updateStorage();
        },

        onResetCollection: function () {
            this.getContainer().empty();
            this.resetForm();
            this.updateStorage();
        },

        updateStorage: function () {
            var data = this.getCollection().toJSON();
            _.each(data, function (value) {
                delete value.id;
            });
            this.options.updateStorage(data);
        },

        handleAddModel: function() {
            var model = this.createNewModel();
            var keys = _.keys(model.attributes);
            if (this.validateFormData(keys)) {
                var data = this.getFormData(keys);
                this.clearFormData(keys);
                model.set(data);
                this.addModel(model);
            }
        },

        handleSaveModel: function(modelId) {
            var model = this.getCollection().get(modelId);
            var keys = _.keys(model.attributes);
            if (this.validateFormData(keys)) {
                model.set(this.getFormData(keys));
                this.resetForm();
            }
        },

        handleDeleteModel: function(modelId) {
            var model = this.getCollection().get(modelId);
            if (this.$el.find(this.selectors.saveButton).data('id') == modelId) {
                this.resetForm();
            }
            this.deleteModel(model);
        },

        handleCancelButton: function() {
            this.resetForm();
        },

        updateColumnSelector: function (data) {
            var emptyText = this.columnSelector.find('option[value=""]').text();
            this.columnSelector.empty();
            this.columnSelector.append(this.optionTemplate({'id': '', 'text': emptyText}));
            _.each(data, _.bind(function (entity) {
                _.each(entity.fields, _.bind(function (field) {
                    this.columnSelector.append(this.optionTemplate({
                        'id': entity['name'] + '::' +field['name'],
                        'text': field['label']
                    }));
                }, this));
            }, this));
            this.columnSelector.val('');
            this.columnSelector.attr('disabled', false);
            this.columnSelector.trigger('change');
        },

        initForm: function () {
            this.form = $(this.options.itemFormSelector);
            this.columnSelector = this.form.find('[data-purpose="column-selector"]');

            var onAdd = _.bind(function (e) {
                e.preventDefault();
                this.handleAddModel();
            }, this);
            this.$el.find(this.selectors.addButton).on('click', onAdd);

            var onSave = _.bind(function (e) {
                e.preventDefault();
                var id = $(e.currentTarget).data('id');
                this.handleSaveModel(id);
            }, this);
            this.$el.find(this.selectors.saveButton).on('click', onSave);

            var onCancel = _.bind(function (e) {
                e.preventDefault();
                this.handleCancelButton();
            }, this);
            this.$el.find(this.selectors.cancelButton).on('click', onCancel);
        },

        toggleFormButtons: function (modelId) {
            if (_.isNull(modelId)) {
                modelId = '';
            }
            var addButton = this.$el.find(this.selectors.addButton);
            var saveButton = this.$el.find(this.selectors.saveButton);
            var cancelButton = this.$el.find(this.selectors.cancelButton);
            saveButton.data('id', modelId);
            if (modelId == '') {
                cancelButton.hide();
                saveButton.hide();
                addButton.show();
            } else {
                addButton.hide();
                cancelButton.show();
                saveButton.show();
            }
        },

        bindItemActions: function (item) {
            // bind edit button
            var onEdit = _.bind(function (e) {
                e.preventDefault();
                var el = $(e.currentTarget);
                var id = el.closest('[data-id]').data('id');
                var model = this.getCollection().get(id);
                this.setFormData(model.attributes);
                this.toggleFormButtons(id);
            }, this);
            item.find(this.selectors.editButton).on('click', onEdit);

            // bind delete button
            var onDelete = _.bind(function (e) {
                e.preventDefault();
                var el = $(e.currentTarget);
                var id = el.closest('[data-id]').data('id');
                var confirm = new DeleteConfirmation({
                    content: el.data('message')
                });
                confirm.on('ok', _.bind(this.handleDeleteModel, this, id));
                confirm.open();
            }, this);
            item.find(this.selectors.deleteButton).on('click', onDelete);
        },

        resetForm: function () {
            this.clearFormData(_.keys((this.createNewModel()).attributes));
            this.toggleFormButtons(null);
        },

        validateFormData: function (keys) {
            var isValid = true;
            this.iterateFormData(keys, function (key, el) {
                FormValidation.removeFieldErrors(el);
                if (el.is('[required]')) {
                    var value = el.val();
                    if (typeof(value) == 'undefined' || null === value || '' === value) {
                        FormValidation.addFieldErrors(el, __('This value should not be blank.'));
                        isValid = false;
                    }
                }
            });

            return isValid;
        },

        getFormData: function (keys) {
            var data = {};
            this.iterateFormData(keys, function (key, el) {
                data[key] = el.val();
            });

            return data;
        },

        clearFormData: function (keys) {
            this.iterateFormData(keys, function (key, el) {
                el.val('');
                el.trigger('change');
            });
        },

        setFormData: function (data) {
            this.iterateFormData(_.keys(data), function (key, el) {
                el.val(data[key]);
                el.trigger('change');
            });
        },

        iterateFormData: function (keys, callback) {
            keys = _.without(keys, 'id');
            var fieldNameRegex = /\[(\w+)\]$/;
            var elements = this.form.find('[name]');
            _.each(elements, function (el) {
                var fieldNameData = fieldNameRegex.exec(el.name);
                if (fieldNameData && fieldNameData.length == 2 && _.indexOf(keys, fieldNameData[1]) !== -1) {
                    callback(fieldNameData[1], $(el));
                }
            });
        },

        getLocalizedText: function (key, value) {
            var el = this.form.find('select[name$="\\[' + key + '\\]"] option[value="' + value + '"]');
            return (el.length === 1) ? el.text() : value;
        },

        createNewModel: function () {
            var modelClass = this.getCollection().model;
            return new modelClass();
        }
    });
});

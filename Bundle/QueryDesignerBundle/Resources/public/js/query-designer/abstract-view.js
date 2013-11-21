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
            entityName: null,
            itemTemplateSelector: null,
            itemFormSelector: null,
            columnChainTemplateSelector: null,
            fieldsLabel: 'Fields',
            relatedLabel: 'Related',
            findEntity: function (entityName) {
                return {name: entityName, label: entityName, plural_label: entityName, icon: null};
            }
        },

        /** @property {Object} */
        selectors: {
            itemContainer:  '.item-container',
            cancelButton:   '.cancel-button',
            saveButton:     '.save-button',
            addButton:      '.add-button',
            editButton:     '.edit-button',
            deleteButton:   '.delete-button',
            columnSelector: '[data-purpose="column-selector"]'
        },

        /** @property */
        columnSelectOptGroupTemplate: _.template('<optgroup label="<%- label %>"'
            + '><%= options %>'
            + '</optgroup>'
        ),

        /** @property */
        columnSelectOptionTemplate: _.template('<option value="<%- name %>"'
            + '<% _.each(_.omit(obj, ["name"]), function (val, key) { %> data-<%- key.replace(/_/g,"-") %>="<%- val %>"<% }) %>'
            + '><%- label %>'
            + '</option>'
        ),

        /** @property {jQuery} */
        form: null,

        /** @property {Array} */
        fieldNames: null,

        /** @property {jQuery} */
        columnSelector: null,

        /** @property {Array} */
        fieldLabelGetters: null,

        /** @property */
        itemTemplate: null,

        /** @property */
        columnChainTemplate: null,

        initialize: function() {
            this.options.collection = this.options.collection || new this.collectionClass();
            this.fieldNames = _.without(_.keys((this.createNewModel()).attributes), 'id');

            this.itemTemplate = _.template($(this.options.itemTemplateSelector).html());
            this.columnChainTemplate = _.template($(this.options.columnChainTemplateSelector).html());

            // prepare field label getters
            this.addFieldLabelGetter(this.getSelectFieldLabel);
            this.addFieldLabelGetter(this.getColumnFieldLabel);

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
            return this.$el.find(this.selectors.itemContainer);
        },

        getColumnSelector: function () {
            return this.columnSelector;
        },

        changeEntity: function (entityName) {
            this.options.entityName = entityName;
            this.getCollection().reset();
        },

        initModel: function (model, index) {
            model.set('id', _.uniqueId('designer'));
        },

        addModel: function(model) {
            this.initModel(model, this.getCollection().size());
            this.getCollection().add(model);
        },

        deleteModel: function(model) {
            this.getCollection().remove(model);
        },

        onModelAdded: function(model) {
            var item = $(this.itemTemplate(this.prepareItemTemplateData(model)));
            this.bindItemActions(item);
            this.getContainer().append(item);
            this.trigger('collection:change');
        },

        onModelChanged: function(model) {
            var item = $(this.itemTemplate(this.prepareItemTemplateData(model)));
            this.bindItemActions(item);
            this.getContainer().find('[data-id="' + model.id + '"]').outerHTML(item);
            this.trigger('collection:change');
        },

        onModelDeleted: function(model) {
            this.getContainer().find('[data-id="' + model.id + '"]').remove();
            this.trigger('collection:change');
        },

        onResetCollection: function () {
            this.getContainer().empty();
            this.resetForm();
            this.getCollection().each(_.bind(function (model, index) {
                this.initModel(model, index);
                var item = $(this.itemTemplate(this.prepareItemTemplateData(model)));
                this.bindItemActions(item);
                this.getContainer().append(item);
            }, this));
            this.trigger('collection:change');
        },

        handleAddModel: function() {
            var model = this.createNewModel();
            if (this.validateFormData()) {
                var data = this.getFormData();
                this.clearFormData();
                model.set(data);
                this.addModel(model);
            }
        },

        handleSaveModel: function(modelId) {
            var model = this.getCollection().get(modelId);
            if (this.validateFormData()) {
                model.set(this.getFormData());
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

        updateColumnSelector: function (columns) {
            if (this.columnSelector.get(0).tagName.toLowerCase() == 'select') {
                var emptyText = this.columnSelector.find('option[value=""]').text();
                this.columnSelector.empty();
                this.columnSelector.append(this.columnSelectOptionTemplate({name: '', label: emptyText}));
                var content = this.getSelectColumnSelectorContent(columns);
                if (content != '') {
                    this.columnSelector.append(content);
                }
            }
            this.columnSelector.val('');
            this.columnSelector.trigger('change');
        },

        getSelectColumnSelectorContent: function (columns) {
            var fields = '';
            var relations = '';
            _.each(columns, _.bind(function (column) {
                if (_.isUndefined(column['related_entity_name'])) {
                    fields += this.columnSelectOptionTemplate(column);
                } else {
                    relations += this.columnSelectOptionTemplate(column);
                }
            }, this));

            if (relations == '') {
                return fields;
            }
            var result = '';
            if (fields != '') {
                result += this.columnSelectOptGroupTemplate({
                    label: this.options.fieldsLabel,
                    options: fields
                });
            }
            result += this.columnSelectOptGroupTemplate({
                label: this.options.relatedLabel,
                options: relations
            });
            return result;
        },

        initForm: function () {
            this.form = $(this.options.itemFormSelector);
            this.columnSelector = this.form.find(this.selectors.columnSelector);

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

        prepareItemTemplateData: function (model) {
            var data = model.toJSON();
            _.each(data, _.bind(function (value, name) {
                data[name] = this.getFieldLabel(name, value);
            }, this));
            return data;
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
            this.clearFormData();
            this.toggleFormButtons(null);
        },

        validateFormData: function () {
            var isValid = true;
            this.iterateFormData(function (name, el) {
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

        getFormData: function () {
            var data = {};
            this.iterateFormData(function (name, field) {
                data[name] = field.val();
            });

            return data;
        },

        clearFormData: function () {
            this.iterateFormData(function (name, field) {
                field.val('').trigger('change');
            });
        },

        setFormData: function (data) {
            this.iterateFormData(function (name, field) {
                field.val(data[name]).trigger('change');
            });
        },

        iterateFormData: function (callback) {
            _.each(this.fieldNames, _.bind(function (name) {
                var field = this.findFormField(name);
                if (field.length === 1) {
                    callback(name, field);
                }
            }, this));
        },

        findFormField: function (name) {
            return this.form.find('[name$="\\[' + name + '\\]"]');
        },

        createNewModel: function () {
            var modelClass = this.getCollection().model;
            return new modelClass();
        },

        addFieldLabelGetter: function (callback) {
            if (_.isNull(this.fieldLabelGetters)) {
                this.fieldLabelGetters = [];
            }
            this.fieldLabelGetters.unshift(callback);
        },

        getFieldLabel: function (name, value) {
            var result = null;
            var field = this.findFormField(name);
            if (field.length == 1) {
                for (var i = 0; i < this.fieldLabelGetters.length; i++) {
                    var callback = this.fieldLabelGetters[i];
                    result = callback.call(this, field, name, value);
                    if (result !== null) {
                        break;
                    }
                }
            }
            return (result !== null ? result : value);
        },

        getSelectFieldLabel: function (field, name, value) {
            if (field.get(0).tagName.toLowerCase() == 'select') {
                var opt = field.find('option[value="' + value + '"]');
                if (opt.length === 1) {
                    return opt.text();
                }
            }
            return null;
        },

        getColumnFieldLabel: function (field, name, value) {
            if (field.attr('name') == this.columnSelector.attr('name')) {
                var columnLabel = null;
                if (this.columnSelector.get(0).tagName.toLowerCase() == 'select') {
                    columnLabel = this.columnSelector
                        .find('option[value="' + value.replace(/\\/g,"\\\\").replace(/:/g,"\\:") + '"]')
                        .data('label');
                } else {
                    columnLabel = value;
                }
                var columns = [];
                _.each(value.split(','), _.bind(function (item, index) {
                    var entity = null;
                    var name = null;
                    if (index == 0) {
                        entity = this.options.entityName;
                        name = item;
                    } else {
                        var pair = item.split('::');
                        entity = pair[0];
                        name = pair[1];
                    }
                    entity = this.options.findEntity(entity);
                    columns.push({
                        entity: entity,
                        label: columnLabel
                    });
                }, this));
                return this.columnChainTemplate(columns);
            }
            return null;
        }
    });
});

'use strict';

/**
 * Form for Project
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'routing',
        'pim/form',
        'oro/loading-mask',
        'oro/messenger',
        'activity-manager/saver/project',
        'datepicker',
        'pim/date-context',
        'pim/datagrid/state',
        'pim/user-context',
        'pim/formatter/date',
        'text!activity-manager/templates/grid/create-project-modal-content',
        'text!activity-manager/templates/field-error'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        Routing,
        BaseForm,
        LoadingMask,
        messenger,
        ProjectSaver,
        Datepicker,
        DateContext,
        DatagridState,
        UserContext,
        DateFormatter,
        template,
        errorTemplate
    ) {
        return BaseForm.extend({
            template: _.template(template),
            errorTemplate: _.template(errorTemplate),
            events: {
                'input .project-field': 'onInputField'
            },
            validationErrors: [],
            datetimepickerOptions: {
                format: DateContext.get('date').format,
                defaultFormat: DateContext.get('date').defaultFormat,
                language: DateContext.get('language')
            },
            maxLengthLabel: 100,
            formType: 'create',

            /**
             * Set the backbone model for this view.
             *
             * @param {Backbone.Model} model
             */
            setModel: function (model) {
                this.model = model;
            },

            /**
             * Set the type of this form. Either "create" or "edit".
             *
             * @param {String} formType
             */
            setFormType: function (formType) {
                this.formType = formType;
            },

            /**
             * When an input occurs, it updates the model, validates fields and partial render form
             * when field values are not valid.
             */
            onInputField: function () {
                var model = this.getFormModel();

                this.updateModel(model);
                this.validateFields(model);
                this.partialRender();
            },

            /**
             * Update the model with data from the js form
             *
             * @param {Object} model
             */
            updateModel: function (model) {
                var dueDate = this.$('[name="project-due-date"]').val();

                if ('' !== dueDate) {
                    dueDate = DateFormatter.format(
                        dueDate,
                        DateContext.get('date').format,
                        'yyyy-MM-dd'
                    );
                }

                model.set('label', this.$('[name="project-label"]').val());
                model.set('description', this.$('[name="project-description"]').val());
                model.set('due_date', dueDate);
            },

            /**
             * Validates fields from the model
             *
             * @param {Object} model
             */
            validateFields: function (model) {
                this.validationErrors = [];
                this.validateLabel(model.get('label'));
                this.validateDueDate(model.get('due_date'));
            },

            /**
             * Check if the model's label respects the max length.
             * Displays a message if too long and triggers an event to toggle the "Next" button of the modal.
             *
             * @param {string} label
             */
            validateLabel: function (label) {
                var isLabelTooLong = (label.length > this.maxLengthLabel);

                if (isLabelTooLong) {
                    this.validationErrors.push({
                        field: 'label',
                        message: __('activity_manager.project.label_maxlength', {max: this.maxLengthLabel})
                    });
                }

                this.trigger(
                    'activity-manager:edit-project:field-validated',
                    'label',
                    !isLabelTooLong
                );
            },

            /**
             * Check if the model's due date isn't in the past.
             * Displays a message if in the past and triggers an event to toggle the "Next" button of the modal.
             *
             * @param {string} modelDueDate
             */
            validateDueDate: function (modelDueDate) {
                var isDueDateInPast = false;

                if (modelDueDate) {
                    var today = new Date().setHours(0, 0, 0, 0);
                    var dueDate = new Date(modelDueDate).setHours(0, 0, 0, 0);

                    isDueDateInPast = today > dueDate;
                }

                if (isDueDateInPast) {
                    this.validationErrors.push({
                        field: 'dueDate',
                        message: __('activity_manager.project.due_date_past')
                    });
                }

                this.trigger(
                    'activity-manager:edit-project:field-validated',
                    'due_date',
                    !isDueDateInPast
                );
            },

            /**
             * Save the form content by posting it to backend
             *
             * @return {Promise}
             */
            save: function () {
                this.validationErrors = [];
                var loadingMask = new LoadingMask();
                var project = _.defaults(this.getFormData(), {label: null});
                project.datagrid_view = DatagridState.get('product-grid', ['filters', 'columns']);
                project.locale = UserContext.get('catalogLocale');

                this.$el.empty().append(loadingMask.render().$el.show());

                return ProjectSaver.save(project)
                    .done(function () {
                        messenger.notificationMessage(
                            'success',
                            __('activity_manager.' + this.formType + '_project_modal.saved')
                        );
                    }.bind(this))
                    .fail(function (response) {
                        if (_.isArray(response.responseJSON)) {
                            this.validationErrors = response.responseJSON;
                        } else {
                            this.validationErrors.push(response.responseJSON);
                        }

                        this.render();
                    }.bind(this))
                    .always(function () {
                        loadingMask.remove();
                    });
            },

            /**
             * Partial render validation part of the form.
             */
            partialRender: function () {
                $('.bootstrap-datetimepicker-widget:visible').hide();
                this.$('.label-errors').empty();
                this.$('.dueDate-errors').empty();

                if (0 < this.validationErrors.length) {
                    _.each(this.validationErrors, function (error) {
                        var selector = '.' + error.field + '-errors';

                        this.$(selector).append(this.errorTemplate({
                            message: error.message
                        }));
                    }.bind(this));
                }
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                var model = this.getFormModel();

                this.$el.html(this.template({
                    labelValue: model.get('label'),
                    labelLabel: __('activity_manager.project.label'),
                    labelPlaceholder: __('activity_manager.' + this.formType + '_project_modal.label_placeholder'),
                    descriptionValue: model.get('description'),
                    descriptionLabel: __('activity_manager.project.description'),
                    descriptionPlaceholder: __(
                        'activity_manager.' + this.formType + '_project_modal.description_placeholder'
                    ),
                    dueDateValue: model.get('due_date'),
                    dueDateLabel: __('activity_manager.project.due_date'),
                    errors: this.validationErrors,
                    requiredLabel: __('activity_manager.common.required')
                }));

                this.initializeDatepicker();

                return this.renderExtensions();
            },

            /**
             * Initialize the datepicker component on the "Due date" input of the project.
             */
            initializeDatepicker: function () {
                Datepicker
                    .init(this.$('.date-wrapper'), this.datetimepickerOptions)
                    .on('show', function () {
                        $('.bootstrap-datetimepicker-widget:visible').css('zIndex', 9999);
                    })
                    .on('changeDate', this.onInputField.bind(this));
            }
        });
    }
);

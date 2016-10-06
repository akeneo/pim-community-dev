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
        'activity-manager/saver/project',
        'datepicker',
        'pim/date-context',
        'text!activity-manager/templates/grid/create-project-modal-content'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        Routing,
        BaseForm,
        LoadingMask,
        ProjectSaver,
        Datepicker,
        DateContext,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'input .project-field': 'updateModel'
            },
            validationErrors: [],
            modelDateFormat: 'yyyy-MM-dd',
            datetimepickerOptions: {
                format: DateContext.get('date').format,
                defaultFormat: DateContext.get('date').defaultFormat,
                language: DateContext.get('language')
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(
                    this.getParent(),
                    'grid:view-selector:create-project:modal-on-cancel',
                    this.onModalCancel.bind(this)
                );

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Update the model with data from the js form
             */
            updateModel: function () {
                this.getFormModel().set('label', this.$('[name="project-label"]').val());
                this.getFormModel().set('description', this.$('[name="project-description"]').val());

                var dueDate = $('[name="project-due-date"]').val();

                if ('' !== dueDate) {
                    var date = this.formatDate(
                        this.$('[name="project-due-date"]').val(),
                        DateContext.get('date').format,
                        this.modelDateFormat
                    );
                }

                this.getFormModel().set('due_date', dueDate);

                this.trigger('grid:view-selector:create-project:model-updated', this.getFormModel());
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

                this.$el.empty().append(loadingMask.render().$el.show());

                return ProjectSaver.save(project)
                    .fail(function (response) {
                        this.validationErrors = response.responseJSON;
                        this.render();
                    }.bind(this))
                    .always(function () {
                        loadingMask.remove();
                    });
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(this.template({
                    requiredLabel: __('activity_manager.common.required'),
                    projectLabel: __('activity_manager.project.label'),
                    projectDescription: __('activity_manager.project.description'),
                    projectDueDate: __('activity_manager.project.due_date'),
                    labelPlaceholder: __('activity_manager.create_project_modal.label_placeholder'),
                    descriptionPlaceholder: __('activity_manager.create_project_modal.description_placeholder'),
                    errors: this.validationErrors
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
                    .on('changeDate', this.updateModel.bind(this));

                $('[name="project-due-date"]').blur(function () {
                    $('.bootstrap-datetimepicker-widget:visible').hide();
                });
            },

            /**
             * Format a date according to specified format.
             * It instantiates a datepicker on-the-fly to perform the conversion. Not possible to use the "real" ones since
             * we need to format a date even when the UI is not initialized yet.
             *
             * @param {String} date
             * @param {String} fromFormat
             * @param {String} toFormat
             *
             * @return {String}
             */
            formatDate: function (date, fromFormat, toFormat) {
                if (_.isArray(date) || _.isEmpty(date)) {
                    return null;
                }

                var options        = $.extend({}, this.datetimepickerOptions, {format: fromFormat});
                var fakeDatepicker = Datepicker.init($('<input>'), options).data('datetimepicker');

                fakeDatepicker.setValue(date);
                fakeDatepicker.format = toFormat;
                fakeDatepicker._compileFormat();

                return fakeDatepicker.formatDate(fakeDatepicker.getDate());
            },

            /**
             * Method called when the parent modal is canceled.
             * We stop listening to the modal since it doesn't exist anymore.
             */
            onModalCancel: function () {
                this.stopListening(this.getParent());
                $('.bootstrap-datetimepicker-widget:visible').hide();
            }
        });
    }
);

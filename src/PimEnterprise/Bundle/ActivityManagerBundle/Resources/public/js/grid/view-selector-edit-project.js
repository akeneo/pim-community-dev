'use strict';

/**
 * Edit project extension for the Datagrid View Selector.
 * It displays a button near the selector to allow the user to edit current project.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/form',
        'text!activity-manager/templates/grid/view-selector/edit-project',
        'activity-manager/project/create-form',
        'pim/formatter/date',
        'pim/date-context',
        'pim/fetcher-registry'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        template,
        CreateForm,
        DateFormatter,
        DateContext,
        FetcherRegistry
    ) {
        return BaseForm.extend({
            template: _.template(template),
            tagName: 'span',
            className: 'edit-button',
            fieldsStatuses: {},
            form: null,
            events: {
                'click .edit': 'promptEditProject'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if ('project' !== this.getRoot().currentViewType) {
                    this.$el.html('');

                    return this;
                }

                this.$el.html(this.template({
                    label: __('activity_manager.grid.view_selector.edit')
                }));

                this.$('[data-toggle="tooltip"]').tooltip();

                return this;
            },

            /**
             * Prompt the edit project modal
             */
            promptEditProject: function () {
                var modal = new Backbone.BootstrapModal({
                    title: __('activity_manager.edit_project_modal.title'),
                    content: '',
                    cancelText: __('activity_manager.edit_project_modal.cancel'),
                    okText: __('activity_manager.edit_project_modal.confirm'),
                    okCloses: false
                });

                modal.open();

                var modalBody = modal.$('.modal-body');
                modalBody.css('min-height', 150);
                modalBody.css('overflow-y', 'hidden');

                FetcherRegistry.getFetcher('project')
                    .fetch(this.getRoot().currentView.label)
                    .then(function (project) {
                        project = $.extend(true, {}, project);
                        project.due_date = DateFormatter.format(project.due_date, 'yyyy-MM-dd', DateContext.get('date').format);

                        this.form = new CreateForm();
                        this.form.configure();
                        this.form.setModel(new Backbone.Model(project));
                        this.form.setType('edit');
                        this.form.on(
                            'activity-manager:edit-project:field-validated',
                            this.onFieldValueValidated.bind(this)
                        );
                        this.form.setElement(modalBody).render();
                    }.bind(this));

                modal.on('cancel', function () {
                    modal.remove();
                }.bind(this));

                modal.on('ok', function () {
                    if ($('.modal .ok').hasClass('AknButton--disabled')) {
                        return;
                    }

                    this.form.save()
                        .done(function () {
                            modal.close();
                            modal.remove();
                            this.getRoot().trigger('grid:view-selector:project-edited');
                        }.bind(this));
                }.bind(this));
            },

            /**
             * Method called on update field value of the modal.
             * It receives the field and the valid status of it to toggle button for example.
             */
            onFieldValueValidated: function (field, isValid) {
                this.fieldsStatuses[field] = isValid;

                if (_.every(_.values(this.fieldsStatuses))) {
                    $('.modal .ok').removeClass('AknButton--disabled');
                } else {
                    $('.modal .ok').addClass('AknButton--disabled');
                }
            }
        });
    }
);

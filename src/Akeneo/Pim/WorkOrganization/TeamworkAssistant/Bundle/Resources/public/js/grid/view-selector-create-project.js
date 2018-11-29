'use strict';

/**
 * Create extension for the Datagrid View Selector.
 * It displays a button near the selector to allow the user to create a new project.
 *
 * @author Adrien Petremann <adrien.petremann@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/form',
        'teamwork-assistant/templates/grid/view-selector/create-project',
        'pim/template/common/modal-with-illustration',
        'teamwork-assistant/project/create-form'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        template,
        templateModal,
        CreateForm
    ) {
        return BaseForm.extend({
            template: _.template(template),
            templateModal: _.template(templateModal),
            tagName: 'span',
            className: 'create-project-button',
            fieldsStatuses: {},
            form: null,
            events: {
                'click': 'promptCreateProject'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!_.contains(this.getRoot().config.viewTypes, 'project')) {
                    return this;
                }
                const label = ('project' === this.getRoot().currentViewType) ?
                    __('teamwork_assistant.grid.view_selector.create') :
                    __('teamwork_assistant.grid.view_selector.create_from_view');

                this.$el.html(this.template({
                    label: label
                }));

                this.delegateEvents();

                return this;
            },

            /**
             * Prompt the create project modal
             */
            promptCreateProject: function () {
                this.form = new CreateForm();
                this.form.configure();
                this.form.on(
                    'teamwork-assistant:edit-project:field-validated',
                    this.onFieldValueValidated.bind(this)
                );

                var modal = new Backbone.BootstrapModal({
                    title: __('pim_common.create'),
                    subtitle: __('teamwork_assistant.create_project_modal.title'),
                    content: this.form,
                    picture: 'illustrations/Project.svg',
                    okText: __('pim_common.save'),
                    template: this.templateModal,
                    okCloses: false,
                });

                modal.open();

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
                        });
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

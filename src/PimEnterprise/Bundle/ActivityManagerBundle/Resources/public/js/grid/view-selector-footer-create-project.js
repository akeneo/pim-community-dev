'use strict';

/**
 * Extension of view-selector-footer-create which add a button to create view.
 * This extension add dropdown with Create view and Create todo instead.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/form',
        'oro/loading-mask',
        'pim/form-builder',
        'text!activity-manager/templates/grid/view-selector/footer/create/project',
        'activity-manager/project/create-form'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        LoadingMask,
        FormBuilder,
        template,
        CreateForm
    ) {
        return BaseForm.extend({
            template: _.template(template),
            tagName: 'span',
            className: 'action',
            events: {
                'click [data-action="prompt-create-project"]': 'promptCreateProject'
            },
            fieldsStatuses: {},
            form: null,

            /**
             * {@inheritDoc}
             */
            render: function () {
                this.$el.html(this.template({
                    projectButtonTitle: __('activity_manager.grid.view_selector.create')
                }));

                return this;
            },

            /**
             * Prompt the create project modal
             */
            promptCreateProject: function () {
                this.getRoot().trigger('grid:view-selector:close-selector');

                var modal = new Backbone.BootstrapModal({
                    title: __('activity_manager.create_project_modal.title'),
                    content: '',
                    cancelText: __('activity_manager.create_project_modal.cancel'),
                    okText: __('activity_manager.create_project_modal.confirm'),
                    okCloses: false
                });

                modal.open();

                var modalBody = modal.$('.modal-body');
                modalBody.css('min-height', 150);
                modalBody.css('overflow-y', 'hidden');

                this.form = new CreateForm();
                this.form.configure();
                this.form.on(
                    'activity-manager:edit-project:field-validated',
                    this.onFieldValueValidated.bind(this)
                );
                this.form.setElement(modalBody).render();

                modal.on('cancel', function () {
                    modal.remove();
                }.bind(this));

                modal.on('ok', function () {
                    if ($('.modal .ok').hasClass('disabled')) {
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
                    $('.modal .ok').removeClass('disabled');
                } else {
                    $('.modal .ok').addClass('disabled');
                }
            }
        });
    }
);

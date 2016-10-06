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
        'oro/loading-mask',
        'pim/form-builder',
        'text!activity-manager/templates/grid/view-selector/footer/create-view',
        'routing',
        'oro/navigation',
        'pim/grid/view-selector/footer/create'
    ],
    function (
        $,
        _,
        __,
        LoadingMask,
        FormBuilder,
        template,
        Routing,
        Navigation,
        BaseFooterCreate
    ) {
        return BaseFooterCreate.extend({
            template: _.template(template),
            events: {
                'click [data-action="prompt-creation"]': 'promptCreation',
                'click [data-action="prompt-creation-project"]': 'promptCreationProject'
            },
            fieldsStatuses: {},

            /**
             * {@inheritDoc}
             */
            render: function () {
                this.$el.html(this.template({
                    viewButtonTitle: __('grid.view_selector.create'),
                    projectButtonTitle: __('activity_manager.grid.view_selector.create')
                }));
                this.$('.dropdown-toggle').dropdown();

                return this;
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(
                    this.getRoot(),
                    'grid:view-selector:create-project:update-field-value',
                    this.onUpdateFieldValue.bind(this)
                );

                return BaseFooterCreate.prototype.configure.apply(this, arguments);
            },

            /**
             * Prompt the create project modal
             */
            promptCreationProject: function () {
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

                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(modalBody).show();

                FormBuilder.buildForm('activity-manager-project-create-form').then(function (form) {
                    form.setParent(this);
                    form.configure().then(function () {
                        form.setElement(modalBody).render();

                        modal.on('cancel', function () {
                            this.trigger('grid:view-selector:create-project:modal-on-cancel');
                            modal.remove();
                        }.bind(this));

                        modal.on('ok', function () {
                            if ($('.modal .ok').hasClass('disabled')) {
                                return;
                            }

                            form.save()
                                .done(function (project) {
                                    modal.close();
                                    modal.remove();

                                    this.redirect(
                                        'activity_manager_project_show',
                                        {label: project.label}
                                    );
                                }.bind(this));
                        }.bind(this));
                    }.bind(this));
                }.bind(this));
            },

            /**
             * Redirect to route with given options
             *
             * @param {string} route
             * @param {object} options
             */
            redirect: function (route, options) {
                var navigation = Navigation.getInstance();

                navigation.setLocation(
                    Routing.generate(
                        route,
                        options
                    )
                );
            },

            /**
             * Method called on update field value of the modal.
             * It receives the field and the valid status of it to toggle button for example.
             *
             * @param {string}  field
             * @param {boolean} isValid
             */
            onUpdateFieldValue: function (field, isValid) {
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

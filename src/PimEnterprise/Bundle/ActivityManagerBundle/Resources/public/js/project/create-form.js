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
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'change input': 'updateModel'
            },
            validationErrors: [],

            /**
             * Update the model with data from the js form
             */
            updateModel: function () {
                this.getFormModel().set('label', this.$('[name="project-label"]').val())
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
                    projectLabel: __('activity_manager.project.label'),
                    labelPlaceholder: __('activity_manager.create_project_modal.label_placeholder'),
                    errors: this.validationErrors
                }));

                return this.renderExtensions();
            }
        });
    }
);

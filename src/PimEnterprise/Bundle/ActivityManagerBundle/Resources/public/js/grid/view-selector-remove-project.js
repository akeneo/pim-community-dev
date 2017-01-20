'use strict';

/**
 * Remove project extension for the Datagrid View Selector.
 * It displays a button near the selector to allow the user to remove current project.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'text!activity-manager/templates/grid/view-selector/remove-project',
        'pim/dialog',
        'pim/user-context',
        'pim/fetcher-registry',
        'activity-manager/remover/project',
        'oro/messenger'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template,
        Dialog,
        UserContext,
        FetcherRegistry,
        ProjectRemover,
        messenger
    ) {
        return BaseForm.extend({
            template: _.template(template),
            tagName: 'span',
            className: 'remove-button',
            fieldsStatuses: {},
            form: null,
            events: {
                'click .remove': 'promptDeletion'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var isProject = ('project' === this.getRoot().currentViewType);
                var isOwner = (UserContext.get('meta').id === this.getRoot().currentView.owner_id);

                if (!isProject || !isOwner) {
                    this.$el.html('');

                    return this;
                }

                this.$el.html(this.template({
                    label: __('activity_manager.grid.view_selector.remove')
                }));

                this.$('[data-toggle="tooltip"]').tooltip();

                return this;
            },

            /**
             * Prompt the datagrid project deletion modal.
             */
            promptDeletion: function () {
                Dialog.confirm(
                    __('activity_manager.grid.view_selector.confirmation.remove'),
                    __('activity_manager.grid.view_selector.confirmation.delete'),
                    function () {
                        this.removeCurrentProject();
                    }.bind(this)
                );
            },

            /**
             * Remove the current Project.
             */
            removeCurrentProject: function () {
                FetcherRegistry.getFetcher('project')
                    .fetch(this.getRoot().currentView.label)
                    .then(function (project) {
                        ProjectRemover.remove(project)
                            .done(function () {
                                this.getRoot().trigger('grid:view-selector:project-removed');
                            }.bind(this))
                            .fail(function (response) {
                                messenger.notificationFlashMessage('error', response.responseJSON);
                            });
                    }.bind(this));
            }
        });
    }
);

'use strict';

/**
 * Project completeness.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'backbone',
        'routing',
        'pim/fetcher-registry',
        'text!activity-manager/templates/widget/project-completeness-data'
    ],
    function ($, _, __, BaseForm, Backbone, Routing, FetcherRegistry, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknProjectWidget-boxes',

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             *
             * Render completeness data of the contributor for the given project.
             * If username is null, it renders global completeness of the project.
             */
            render: function () {
                var data = this.getFormData();
                var contributorUsername = null;

                if (this.getFormModel().has('currentContributorUsername')) {
                    contributorUsername = data.currentContributorUsername;
                }

                FetcherRegistry.getFetcher('project').getCompleteness(data.currentProjectCode, contributorUsername)
                    .then(function (completeness) {
                        this.$el.html(this.template({
                            completeness: completeness,
                            todoLabel: __(this.config.labels.todo),
                            inProgressLabel: __(this.config.labels.inProgress),
                            displayProductsLabel: __(this.config.labels.displayProducts),
                            ratioTodoLabel: __(this.config.labels.ratioTodo),
                            ratioInProgressLabel: __(this.config.labels.ratioInProgress),
                            ratioDoneLabel: __(this.config.labels.ratioDone),
                            doneLabel: __(this.config.labels.done),
                            url: Routing.generate(
                                'activity_manager_project_show',
                                {identifier: data.currentProjectCode}
                            )
                        }));
                    }.bind(this));
            }
        });
    }
);

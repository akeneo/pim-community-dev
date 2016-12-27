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
        'pim/fetcher-registry',
        'text!activity-manager/templates/widget/project-completeness-data',
        'activity-manager/project/completeness-formatter'
    ],
    function ($, _, __, BaseForm, Backbone, FetcherRegistry, template, completenessFormatter) {
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
                        var completenessProgress = completenessFormatter.getCompletenessProgress(completeness);
                        completenessProgress.todo += '% ' + __(this.config.labels.percentageTodo);
                        completenessProgress.in_progress += '% ' + __(this.config.labels.percentageInProgress);
                        completenessProgress.done += '% ' + __(this.config.labels.percentageDone);

                        this.$el.html(this.template({
                            completeness: completeness,
                            percentage: completenessProgress,
                            todoLabel: __(this.config.labels.todo),
                            inProgressLabel: __(this.config.labels.inProgress),
                            doneLabel: __(this.config.labels.done)
                        }));
                    }.bind(this));
            }
        });
    }
);

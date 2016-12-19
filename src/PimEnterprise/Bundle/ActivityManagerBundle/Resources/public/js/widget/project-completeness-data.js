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
        'text!activity-manager/templates/widget/project-completeness-data'
    ],
    function ($, _, __, BaseForm, Backbone, FetcherRegistry, template) {
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
                            percentage: this.formatToPercentage(completeness),
                            todoLabel: __(this.config.labels.todo),
                            inProgressLabel: __(this.config.labels.inProgress),
                            doneLabel: __(this.config.labels.done)
                        }));
                    }.bind(this));
            },

            /**
             * Format a number to a percentage with the linked sentence.
             *
             * @param {Collection} completeness
             *
             * @returns {Collection}
             */
            formatToPercentage: function (completeness) {
                var rawTodo = parseInt(completeness.todo);
                var rawInProgress = parseInt(completeness.in_progress);
                var rawDone = parseInt(completeness.done);
                var todo = 0, inProgress = 0, done = 0;
                var total = rawTodo + rawInProgress + rawDone;

                if (0 !== total) {
                    todo = Math.round(rawTodo * 100 / total);
                    inProgress = Math.round(rawInProgress * 100 / total);
                    done = Math.round(rawDone * 100 / total);
                }

                return {
                    todo: todo + '% ' + __(this.config.labels.percentageTodo),
                    in_progress: inProgress + '% ' + __(this.config.labels.percentageInProgress),
                    done: done + '% ' + __(this.config.labels.percentageDone)
                };
            }
        });
    }
);

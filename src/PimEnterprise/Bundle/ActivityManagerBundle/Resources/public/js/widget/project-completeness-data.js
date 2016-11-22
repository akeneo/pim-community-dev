define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/fetcher-registry',
        'text!activity-manager/templates/widget/project-completeness-data'
    ],
    function ($, _, __, Backbone, FetcherRegistry, template) {
        'use strict';

        return Backbone.View.extend({
            template: _.template(template),
            projectCode: null,
            username: null,
            className: 'AknProjectWidget-boxes',

            /**
             * @param {int}    projectCode
             * @param {String} username
             */
            initialize: function (projectCode, username) {
                if (_.isUndefined(username) || 'string' !== typeof username) {
                    username = null;
                }

                this.projectCode = projectCode;
                this.username = username;
            },

            /**
             * Render completeness data of the contributor for the given project.
             * If username is null, it renders global completeness of the project.
             */
            render: function () {
                FetcherRegistry.getFetcher('project').getCompleteness(this.projectCode, this.username).then(
                    function (completeness) {
                        var todo = completeness.todo;
                        var inProgress = completeness.in_progress;
                        var done = completeness.done;
                        var total = todo + inProgress + done;

                        this.$el.html(this.template({
                            todo: todo,
                            inProgress: inProgress,
                            done: done,
                            todoLabel: __('activity_manager.widget.todo'),
                            inProgressLabel: __('activity_manager.widget.in_progress'),
                            doneLabel: __('activity_manager.widget.done'),
                            percentageToEnrich: this.formatToPercentage(todo, total, 'to_enrich'),
                            percentageInProgress: this.formatToPercentage(inProgress, total, 'in_progress'),
                            percentageDone: this.formatToPercentage(done, total, 'done')
                        }));
                    }.bind(this)
                );
            },

            /**
             * Format a number to a percentage with the linked sentence.
             *
             * @param {int}    number
             * @param {int}    total
             * @param {String} translationKey
             *
             * @returns {string}
             */
            formatToPercentage: function (number, total, translationKey) {
                var percentage = Math.round(number * 100 / total);
                var translation = __('activity_manager.widget.progress.' + translationKey);

                return percentage + '% ' + translation;
            }
        });
    }
);

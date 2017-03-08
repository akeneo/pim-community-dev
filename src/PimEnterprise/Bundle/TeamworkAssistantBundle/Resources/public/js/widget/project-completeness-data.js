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
        'pim/user-context',
        'text!teamwork-assistant/templates/widget/project-completeness-data'
    ],
    function ($, _, __, BaseForm, Backbone, Routing, FetcherRegistry, UserContext, template) {
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

                var displayLinks = _.contains([UserContext.get('username'), null], contributorUsername);
                var urls = {};

                _.each(['todo', 'inprogress', 'done'], function (status) {
                    urls[status] = Routing.generate('teamwork_assistant_project_show', {
                        identifier: data.currentProjectCode,
                        status: (null === contributorUsername) ? 'owner-' + status : 'contributor-' + status
                    });
                });

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
                            displayLinks: displayLinks,
                            urls: urls
                        }));
                    }.bind(this));
            }
        });
    }
);

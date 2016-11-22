define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/dashboard/abstract-widget',
        'text!activity-manager/templates/widget/project-widget',
        'text!activity-manager/templates/widget/project-widget-empty',
        'pim/user-context',
        'pim/fetcher-registry',
        'activity-manager/widget/project-selector',
        'activity-manager/widget/contributor-selector',
        'activity-manager/widget/project-due-date',
        'activity-manager/widget/project-completeness-data',
        'activity-manager/widget/project-description'
    ],
    function (
        $,
        _,
        __,
        AbstractWidget,
        template,
        templateEmpty,
        UserContext,
        FetcherRegistry,
        ProjectSelector,
        ContributorSelector,
        ProjectDueDate,
        ProjectCompletenessData,
        ProjectDescription
    ) {
        'use strict';

        return AbstractWidget.extend({
            template: _.template(template),
            templateEmpty: _.template(templateEmpty),

            render: function () {
                FetcherRegistry.initialize().then(function () {
                    FetcherRegistry.getFetcher('project').search({search: null, limit: 1, page: 1}).then(
                        function (projects) {
                            if (!_.isEmpty(projects)) {
                                this.$el.html(this.template());

                                this.renderProjectSelector();
                                this.renderProjectInformation(_.first(projects));
                            } else {
                                this.$el.html(this.templateEmpty({message: __('activity_manager.widget.no_project')}));
                            }
                        }.bind(this)
                    );
                }.bind(this));

                return this;
            },

            /**
             * Render all data linked to the project
             *
             * @param {Object} project
             */
            renderProjectInformation: function (project) {
                if (UserContext.get('username') === project.owner.username) {
                    this.renderContributorSelector(project.code);
                    this.renderCompletenessData(project.code);
                } else {
                    this.renderCompletenessData(project.code, UserContext.get('username'));
                }
                this.renderDueDate(project.due_date);
                this.renderDescription(project.description);
            },

            /**
             * Render the project selector populated with projects the current user is allowed to access
             */
            renderProjectSelector: function () {
                var projectSelector = new ProjectSelector();

                projectSelector.render();
                this.listenTo(projectSelector, 'activity-manager.widget.project-selected', function (event) {
                    var project = {
                        code: event.added.id,
                        due_date: event.added.due_date,
                        description: event.added.description,
                        owner: event.added.owner
                    };
                    this.renderProjectInformation(project);
                }.bind(this));
                this.$('.activity-manager-widget-project-selector').html(projectSelector.$el);
            },

            /**
             * Render a select2 populated by the given project contributors
             *
             * @param {String} projectCode
             */
            renderContributorSelector: function (projectCode) {
                var contributorSelector = new ContributorSelector(projectCode);

                contributorSelector.render();
                this.listenTo(contributorSelector, 'activity-manager.widget.contributor-selected', function (event) {
                    var username = event.added.id;

                    this.renderCompletenessData(contributorSelector.getProjectCode(), username);
                }.bind(this));
                this.$('.activity-manager-widget-contributor-selector').html(contributorSelector.$el);
            },

            /**
             * Render the localized due date
             *
             * @param {String} dueDate To the model format yyyy-MM-dd
             */
            renderDueDate: function (dueDate) {
                var projectDueDate = new ProjectDueDate(dueDate);

                projectDueDate.render();
                this.$('.activity-manager-widget-due-date').html(projectDueDate.$el);
            },

            /**
             * Render the description
             *
             * @param {String} description
             */
            renderDescription: function (description) {
                var projectDescription = new ProjectDescription(description);

                projectDescription.render();
                this.$('.activity-manager-widget-description').html(projectDescription.$el);
            },

            /**
             * Render completeness data of the contributor for the given project.
             * If username is null, it renders global completeness of the project.
             *
             * @param {int}    projectCode
             * @param {String} username
             */
            renderCompletenessData: function (projectCode, username) {
                if (_.isUndefined(username) || 'string' !== typeof username) {
                    username = null;
                }
                var projectCompletenessData = new ProjectCompletenessData(projectCode, username);

                projectCompletenessData.render();
                this.$('.activity-manager-widget-completeness').html(projectCompletenessData.$el);
            }
        });
    }
);

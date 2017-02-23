'use strict';

/**
 * Project widget.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'text!team-work-assistant/templates/widget/project-widget',
        'text!team-work-assistant/templates/widget/project-widget-empty',
        'pim/user-context',
        'pim/fetcher-registry',
        'oro/loading-mask',
        'routing'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template,
        templateEmpty,
        UserContext,
        FetcherRegistry,
        LoadingMask,
        Routing
    ) {
        return BaseForm.extend({
            template: _.template(template),
            templateEmpty: _.template(templateEmpty),

            /**
             * {@inheritDoc}
             */
            configure: function () {
                this.onExtensions('team-work-assistant:widget:project-selected', this.updateCurrentProjectCode.bind(this));
                this.onExtensions(
                    'team-work-assistant:widget:contributor-selected',
                    this.updateCurrentContributorUsername.bind(this)
                );

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritDoc}
             */
            render: function () {
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.$el);
                loadingMask.show();

                $.when(
                    this.fetchProject(),
                    this.fetchContributor()
                ).then(function (project, contributor) {
                        if (!_.isEmpty(project)) {
                            this.$el.html(this.template());

                            this.setData({currentProjectCode: project.code});
                            this.setData({currentProject: project});

                            if (!_.isEmpty(contributor)) {
                                this.setData({currentContributorUsername: contributor.username});
                                this.setData({currentContributor: contributor});
                            }

                            this.renderExtensions();
                        } else {
                            this.$el.html(this.templateEmpty({
                                message: __('team_work_assistant.widget.no_project'),
                                messageCreate: __('team_work_assistant.widget.no_project_create'),
                                url: Routing.generate('pim_enrich_product_index')
                            }));
                        }

                        loadingMask.hide();
                    }.bind(this));

                return this;
            },

            /**
             * Fetch project.
             * If a project is set in the model, return the project.
             * If not, return the project that as the nearest due date.
             *
             * @return {Promise}
             */
            fetchProject: function () {
                if (this.getFormModel().has('currentProjectCode')) {
                    return FetcherRegistry.getFetcher('project').fetch(this.getFormData().currentProjectCode);
                }

                return FetcherRegistry.getFetcher('project')
                    .search({search: null, options: {limit: 1, page: 1}})
                    .then(function (projects) {
                        return _.first(projects);
                    });
            },

            /**
             * Fetch the contributor from the model currentContributorUsername.
             *
             * @return {Promise}
             */
            fetchContributor: function () {
                if (!this.getFormModel().has('currentContributorUsername')) {
                    return $.Deferred().resolve({}).promise();
                }

                return FetcherRegistry.getFetcher('user').fetch(this.getFormData().currentContributorUsername);
            },

            /**
             * Update the current project code in the model and render the widget.
             *
             * @param {String} projectCode
             */
            updateCurrentProjectCode: function (projectCode) {
                this.setData({currentProjectCode: projectCode});
                this.render();
            },

            /**
             * Update the current contributor username in the model (if username is not null)
             * and render the widget.
             *
             * @param {String|null} username
             */
            updateCurrentContributorUsername: function (username) {
                this.getFormModel().unset('currentContributorUsername');
                this.getFormModel().unset('currentContributor');
                if (null !== username) {
                    this.setData({currentContributorUsername: username});
                }
                this.render();
            }
        });
    }
);

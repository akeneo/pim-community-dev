'use strict';

/**
 * Override of the module of the datagrid View Selector.
 * We override this module to initialize the selector on projects if the user has
 * project to work on.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pimcommunity/grid/view-selector/selector',
        'pim/datagrid/state',
        'pim/fetcher-registry'
    ],
    function (
        $,
        _,
        __,
        ViewSelector,
        DatagridState,
        FetcherRegistry
    ) {
        return ViewSelector.extend({
            hasNoProject: false,

            /**
             * {@inheritdoc}
             */
            configure: function (gridAlias) {
                this.gridAlias = gridAlias;

                if ('product-grid' !== this.gridAlias) {
                    this.config.viewTypes = ['views'];
                }

                this.listenTo(this.getRoot(), 'grid:view-selector:project-edited', this.onProjectEdited.bind(this));
                this.listenTo(this.getRoot(), 'grid:view-selector:project-removed', this.onProjectRemoved.bind(this));

                return ViewSelector.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             *
             * We define the default view type if the current user has a project as current view.
             */
            initializeViewTypes: function () {
                if (null === this.currentViewType) {
                    if (null !== this.currentView) {
                        this.currentViewType = 'project' === this.currentView.type ? 'project' : 'view';
                    } else {
                        return ViewSelector.prototype.initializeViewTypes.apply(this, arguments);
                    }
                }
            },

            /**
             * {@inheritdoc}
             *
             * Override to handle teamwork assistant projects.
             */
            switchViewType: function (event) {
                const viewType = $(event.target).data('value');

                if (this.currentViewType === viewType) {
                    return;
                }

                this.$('.current-view-type').html(this.$('[data-value="' + viewType + '"]').html());
                this.$('.select2-selection-label-view .current').html(
                    __('teamwork_assistant.grid.view_selector.loading')
                );
                this.select2Instance.select2('readonly', true);

                this.currentViewType = viewType;
                DatagridState.set(this.gridAlias, 'view', '0');

                if ('project' === this.currentViewType) {
                    FetcherRegistry.getFetcher('project')
                        .search({search: null, options: {limit: 1, page: 1}})
                        .then(function (projects) {
                            var project = _.first(projects);
                            this.hasNoProject = (undefined === project);

                            if (this.hasNoProject) {
                                this.disableSelect2();
                                this.renderExtensions();
                            } else {
                                this.selectView(project);
                            }
                        }.bind(this));
                }

                if ('view' === this.currentViewType) {
                    this.initializeSelection().then(function (view) {
                        this.selectView(view);
                    }.bind(this));
                }

                this.render();
            },

            /**
             * Method called when a project has been edited.
             */
            onProjectEdited: function () {
                FetcherRegistry.getFetcher('datagrid-view').clear();
                FetcherRegistry.getFetcher('project').clear();

                this.reloadPage();
            },

            /**
             * Method called when a project has been removed.
             */
            onProjectRemoved: function () {
                FetcherRegistry.getFetcher('project').clear();
                this.currentViewType = 'view';

                this.selectView(this.getDefaultView());
            },

            /**
             * {@inheritdoc}
             *
             * Override to handle teamwork assistant projects view.
             */
            selectView: function (view) {
                if ('project' === this.currentViewType) {
                    var project = view;
                    view = project.datagridView;
                    DatagridState.set('product-grid', 'scope', project.channel.code);
                }

                ViewSelector.prototype.selectView.apply(this, [view]);
            },

            /**
             * {@inheritdoc}
             *
             * Override to fetch the project label of a view
             */
            postFetchDatagridView: function (view) {
                if ('project' === view.type) {
                    return FetcherRegistry.getFetcher('project')
                        .fetch(view.label).then(function (project) {
                            view.text = project.label;

                            return view;
                        });
                }

                return ViewSelector.prototype.postFetchDatagridView.apply(this, arguments);
            },

            /**
             * Disable the View Selector select2 and display a message to create a new project
             */
            disableSelect2: function () {
                this.$('.select2-selection-label-view .current').html(
                    __('teamwork_assistant.grid.view_selector.start_new_project')
                );
                this.$('.select2-arrow').remove();
                this.select2Instance.select2('readonly', true);
            },

            /**
             * {@inheritdoc}
             *
             * Override to disable the select2 if there is no project to display
             */
            initializeSelectWidget: function () {
                ViewSelector.prototype.initializeSelectWidget.apply(this, arguments);

                if ('project' === this.currentViewType && this.hasNoProject) {
                    this.disableSelect2();
                }
            },

            /**
             * {@inheritdoc}
             *
             * Override to set a limit of 3 to fetch projects
             */
            getResultsPerPage: function () {
                if ('project' === this.currentViewType) {
                    return this.config.maxProjectFetching;
                }

                return ViewSelector.prototype.getResultsPerPage.apply(this, arguments);
            }
        });
    }
);

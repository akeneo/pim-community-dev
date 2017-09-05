'use strict';

/**
 * Main module for the Datagrid View Selector.
 * Mainly composed by a Select2 component with several extension points.
 *
 * Allow the user to search & select a Grid View in a list.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/form',
        'pim/template/grid/view-selector',
        'pim/template/grid/view-switcher',
        'pim/initselect2',
        'pim/datagrid/state',
        'pim/fetcher-registry',
        'pim/form-builder'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        template,
        viewSwitcherTemplate,
        initSelect2,
        DatagridState,
        FetcherRegistry,
        FormBuilder
    ) {
        return BaseForm.extend({
            template: _.template(template),
            viewSwitcherTemplate: _.template(viewSwitcherTemplate),
            resultsPerPage: 20,
            queryTimer: null,
            config: {},
            currentViewType: null,
            currentView: null,
            initialView: null,
            defaultColumns: [],
            defaultUserView: null,
            gridAlias: null,
            select2Instance: null,
            viewTypeSwitcher: null,
            currentLoadingPage: null,
            currentLoadingTerm: null,

            events: {
                'click .view-type-item': 'switchViewType'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function (gridAlias) {
                this.gridAlias = gridAlias;

                if (_.has(__moduleConfig, 'forwarded-events')) {
                    this.forwardMediatorEvents(__moduleConfig['forwarded-events']);
                }

                this.listenTo(this.getRoot(), 'grid:view-selector:view-created', this.onViewCreated.bind(this));
                this.listenTo(this.getRoot(), 'grid:view-selector:view-saved', this.onViewSaved.bind(this));
                this.listenTo(this.getRoot(), 'grid:view-selector:view-removed', this.onViewRemoved.bind(this));
                this.listenTo(this.getRoot(), 'grid:view-selector:close-selector', this.closeSelect2.bind(this));
                this.listenTo(this.getRoot(), 'grid:product-grid:state_changed', this.onGridStateChange.bind(this));

                Backbone.Router.prototype.on('route', this.unbindEvents.bind(this));

                return FetcherRegistry.getFetcher('datagrid-view')
                    .defaultColumns(this.gridAlias)
                    .then(function (columns) {
                        this.defaultColumns = columns;

                        return BaseForm.prototype.configure.apply(this, arguments);
                    }.bind(this));
            },

            /**
             * Detach event listeners
             */
            unbindEvents: function () {
                this.off();
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.initializeSelection().then(function () {
                    this.initializeViewTypes();

                    this.$el.html(this.template({
                        __: __,
                        currentViewType: this.currentViewType
                    }));

                    this.initializeSelectWidget();
                    this.renderExtensions();
                }.bind(this));
            },

            /**
             * Initialize the view type to display at initialization.
             */
            initializeViewTypes: function () {
                this.currentViewType = 'view';
            },

            /**
             * Initialize the Select2 component and format elements.
             */
            initializeSelectWidget: function () {
                var $select = this.$('input[type="hidden"]');

                var options = {
                    dropdownCssClass: 'grid-view-selector',
                    closeOnSelect: false,

                    /**
                     * Format result (datagrid view list) method of select2.
                     * This way we can display views and their infos beside them.
                     */
                    formatResult: function (item, $container) {
                        FormBuilder.build('pim-grid-view-selector-line').then(function (form) {
                            form.setParent(this);
                            form.setView(item, this.currentViewType, this.currentView.id);
                            $container.append(form.render().$el);
                        }.bind(this));
                    }.bind(this),

                    /**
                     * Format current selection method of select2.
                     */
                    formatSelection: function (item, $container) {
                        FormBuilder.buildForm('pim-grid-view-selector-current').then(function (form) {
                            form.setParent(this);
                            form.setView(item);

                            return form.configure().then(function () {
                                $container.append(form.render().$el);
                                this.onGridStateChange();
                            }.bind(this));
                        }.bind(this));
                    }.bind(this),

                    query: function (options) {
                        clearTimeout(this.queryTimer);
                        this.queryTimer = setTimeout(function () {

                            var page = 1;
                            if (options.context && options.context.page) {
                                page = options.context.page;
                            }

                            var searchParameters = this.getSelectSearchParameters(options.term, page);
                            var fetcher = this.config.fetchers[this.currentViewType];

                            if (this.currentLoadingPage === page && this.currentLoadingTerm === options.term) {
                                return;
                            }

                            this.currentLoadingPage = page;
                            this.currentLoadingTerm = options.term;

                            FetcherRegistry.getFetcher(fetcher).search(searchParameters).then(function (views) {
                                var choices = this.toSelect2Format(views);

                                if (page === 1 && !options.term) {
                                    choices = this.ensureDefaultView(choices);
                                }

                                options.callback({
                                    results: choices,
                                    more: choices.length === this.getResultsPerPage(),
                                    context: {
                                        page: page + 1
                                    }
                                });
                            }.bind(this));
                        }.bind(this), 400);
                    }.bind(this),

                    /**
                     * Initialize the select2 with current selected view. If no current view is selected,
                     * we select the user's one. If he doesn't have one, we create a default one for him!
                     */
                    initSelection: function (element, callback) {
                        callback(this.currentView);
                    }.bind(this)
                };

                this.select2Instance = initSelect2.init($select, options);
                this.select2Instance.on('select2-selecting', function (event) {
                    var view = event.object;
                    this.selectView(view);
                }.bind(this));

                this.select2Instance.on('select2-close', function () {
                    this.currentLoadingPage = null;
                    this.currentLoadingTerm = null;
                }.bind(this));

                if (this.config.viewTypes.length > 1) {
                    this.$el.find('.select2-search').append(this.viewSwitcherTemplate({
                        __: __,
                        viewTypes: this.config.viewTypes,
                        currentViewType: this.currentViewType,
                    }));
                }
            },

            /**
             * Method called on view type switching.
             *
             * @param {Event} event
             */
            switchViewType: function (event) {
                this.currentViewType = $(event.target).data('value');

                this.render();
            },

            /**
             * Initialize the Select2 selection based on the DatagridState.
             * Could be the User default one, or an existing view edited or whatever.
             *
             * @return {Promise}
             */
            initializeSelection: function () {
                var activeViewId = DatagridState.get(this.gridAlias, 'view');
                var isDefaultView = ('0' === activeViewId);
                var deferred = $.Deferred();

                this.getUserDefaultView().then(function (userDefaultView) {
                    if (userDefaultView && (!activeViewId || isDefaultView)) {
                        // User is on default view but has a custom default one
                        userDefaultView.text = userDefaultView.label;
                        deferred.resolve(userDefaultView);
                    } else if (activeViewId && !isDefaultView) {
                        // User is on an existing view
                        FetcherRegistry.getFetcher('datagrid-view')
                            .fetch(activeViewId, {alias: this.gridAlias, cached: false})
                            .then(this.postFetchDatagridView.bind(this))
                            .then(function (view) {
                                deferred.resolve(view);
                            })
                            .fail(function () {
                                this.selectView(userDefaultView ? userDefaultView : this.getDefaultView());
                            }.bind(this));
                    } else {
                        // Other, set the default view
                        deferred.resolve(this.getDefaultView());
                    }
                }.bind(this));

                deferred.then(function (initView) {
                    var datagridState = DatagridState.get(this.gridAlias, ['filters', 'columns']);

                    this.initialView = $.extend(true, {}, initView);
                    this.currentView = $.extend(true, {}, initView);

                    if (0 !== this.initialView.id && datagridState.columns !== null) {
                        this.currentView.filters = datagridState.filters;
                        this.currentView.columns = datagridState.columns.split(',');
                    }

                    this.getRoot().trigger('grid:view-selector:initialized', this.currentView);

                    return initView;
                }.bind(this));

                return deferred;
            },

            /**
             * Method called right after fetching the view from the backend.
             * This is where we can handle the view before it goes to select2.
             *
             * @param {Object} view
             *
             * @return {Promise}
             */
            postFetchDatagridView: function (view) {
                view.text = view.label;

                return $.Deferred().resolve(view).promise();
            },

            /**
             * Return the default view object which contains default columns & no filter.
             *
             * @return {Object}
             */
            getDefaultView: function () {
                return {
                    id: 0,
                    text: __('grid.view_selector.default_view'),
                    columns: this.defaultColumns,
                    type: 'view',
                    filters: ''
                };
            },

            /**
             * Return the default user view object.
             *
             * @return {Object}
             */
            getUserDefaultView: function () {
                return FetcherRegistry.getFetcher('datagrid-view')
                    .defaultUserView(this.gridAlias)
                    .then(function (defaultUserView) {
                        this.defaultUserView = defaultUserView.view;

                        return defaultUserView.view;
                    }.bind(this));
            },

            /**
             * Ensure given choices contain a default view if user doesn't have one.
             *
             * @param {array} choices
             *
             * @return {array}
             */
            ensureDefaultView: function (choices) {
                if (null !== this.defaultUserView || 'view' !== this.currentViewType) {
                    return choices;
                }

                choices.push(this.getDefaultView());

                return choices;
            },

            /**
             * Method called when the grid state changes.
             * It allows this selector to react to new filters / columns etc..
             */
            onGridStateChange: function () {
                var datagridState = DatagridState.get(this.gridAlias, ['filters', 'columns']);
                if (null === datagridState.columns) {
                    datagridState.columns = '';
                }

                if (null !== this.currentView) {
                    this.currentView.filters = datagridState.filters;
                    this.currentView.columns = datagridState.columns.split(',');
                }

                this.getRoot().trigger('grid:view-selector:state-changed', datagridState);
            },

            /**
             * Method called when a new view has been created.
             * This method fetches the newly created view thanks to its id, then selects it.
             *
             * @param {int} viewId
             */
            onViewCreated: function (viewId) {
                FetcherRegistry.getFetcher('datagrid-view').clear();
                FetcherRegistry.getFetcher('datagrid-view')
                    .fetch(viewId, {alias: this.gridAlias})
                    .then(function (view) {
                        this.selectView(view);
                    }.bind(this));
            },

            /**
             * Method called when a view has been saved.
             * This method fetches the saved view thanks to its id, then selects it.
             *
             * @param {int} viewId
             */
            onViewSaved: function (viewId) {
                this.onViewCreated(viewId);
            },

            /**
             * Method called when a view is removed.
             * We reset all filters on the grid.
             */
            onViewRemoved: function () {
                FetcherRegistry.getFetcher('datagrid-view').clear();
                this.selectView(this.getDefaultView());
            },

            /**
             * Close the Select2 instance of this View Selector
             */
            closeSelect2: function () {
                if (null !== this.select2Instance) {
                    this.select2Instance.select2('close');
                }
            },

            /**
             * Method called when the user selects a view through this selector.
             *
             * @param {Object} view The selected view
             */
            selectView: function (view) {
                DatagridState.set(this.gridAlias, {
                    view: view.id,
                    filters: view.filters,
                    columns: view.columns.join(',')
                });

                this.currentView = view;
                this.trigger('grid:view-selector:view-selected', view);
                this.reloadPage();
            },

            /**
             * Get grid view fetcher search parameters by giving select2 search term & page
             *
             * @param {string} term
             * @param {int}    page
             *
             * @return {Object}
             */
            getSelectSearchParameters: function (term, page) {
                return $.extend(true, {}, this.config.searchParameters, {
                    search: term,
                    alias: this.gridAlias,
                    options: {
                        limit: this.getResultsPerPage(),
                        page: page
                    }
                });
            },

            /**
             * Take incoming data and format them to have all required parameters
             * to be used by the select2 module.
             *
             * @param {array} data
             *
             * @return {array}
             */
            toSelect2Format: function (data) {
                return _.map(data, function (view) {
                    view.text = view.label;

                    if (!_.has(view, 'id') && _.has(view, 'code')) {
                        view.id = view.code;
                    }

                    return view;
                });
            },

            /**
             * Reload the page.
             */
            reloadPage: function () {
                var url = window.location.hash;
                Backbone.history.fragment = new Date().getTime();
                Backbone.history.navigate(url, true);
            },

            getResultsPerPage: function () {
                return this.resultsPerPage;
            }
        });
    }
);

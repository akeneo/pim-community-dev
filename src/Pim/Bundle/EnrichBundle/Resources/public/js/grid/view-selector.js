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
        'pim/grid/view-selector/line',
        'pim/grid/view-selector/footer',
        'pim/grid/view-selector/type-switcher',
        'text!pim/template/grid/view-selector',
        'pim/initselect2',
        'pim/datagrid/state',
        'pim/fetcher-registry',
        'pim/form-builder',
        'module'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        ViewSelectorLine,
        ViewSelectorFooter,
        ViewSelectorTypeSwitcher,
        template,
        initSelect2,
        DatagridState,
        FetcherRegistry,
        FormBuilder,
        module
    ) {
        return BaseForm.extend({
            template: _.template(template),
            resultsPerPage: 20,
            queryTimer: null,
            config: {},
            viewTypes: [],
            currentViewType: null,
            currentView: null,
            initialView: null,
            defaultColumns: [],
            defaultUserView: null,
            gridAlias: null,
            $select2Instance: null,

            /**
             * {@inheritdoc}
             */
            configure: function (gridAlias) {
                this.gridAlias = gridAlias;

                if (_.has(module.config(), 'forwarded-events')) {
                    this.forwardMediatorEvents(module.config()['forwarded-events']);
                }

                this.viewTypes = module.config().view_types;

                this.listenTo(this.getRoot(), 'grid:view-selector:view-created', this.onViewCreated.bind(this));
                this.listenTo(this.getRoot(), 'grid:view-selector:view-saved', this.onViewSaved.bind(this));
                this.listenTo(this.getRoot(), 'grid:view-selector:view-removed', this.onViewRemoved.bind(this));
                this.listenTo(this.getRoot(), 'grid:view-selector:close-selector', this.closeSelect2.bind(this));
                this.listenTo(this.getRoot(), 'grid:product-grid:state_changed', this.onGridStateChange.bind(this));

                return $.when(
                    FetcherRegistry.getFetcher('datagrid-view').defaultColumns(this.gridAlias),
                    FetcherRegistry.getFetcher('datagrid-view').defaultUserView(this.gridAlias)
                ).then(function (columns, defaultView) {
                    this.defaultColumns = columns;
                    this.defaultUserView = defaultView.view;

                    return BaseForm.prototype.configure.apply(this, arguments);
                }.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template());

                this.initializeViewTypes().then(function () {
                    this.initializeSelectWidget();
                    this.renderExtensions();
                }.bind(this));
            },

            /**
             * Initialize the view type to display at initialization.
             *
             * @returns {Promise}
             */
            initializeViewTypes: function () {
                this.currentViewType = 'view';
                // TODO: IF PROJECT/VIEW ALREADY SELECTED, PICK THE RIGHT VIEW TYPE

                return $.Deferred().resolve();
            },

            /**
             * Initialize the Select2 component and format elements.
             */
            initializeSelectWidget: function () {
                var $select = this.$('input[type="hidden"]');

                var options = {
                    dropdownCssClass: 'bigdrop grid-view-selector',
                    closeOnSelect: false,

                    /**
                     * Format result (datagrid view list) method of select2.
                     * This way we can display views and their infos beside them.
                     */
                    formatResult: function (item, $container) {
                        FormBuilder.buildForm('pim-grid-view-selector-line').then(function (form) {
                            form.setParent(this);
                            return form.configure(item).then(function () {
                                $container.append(form.render().$el);
                            });
                        }.bind(this));
                    }.bind(this),

                    /**
                     * Format current selection method of select2.
                     */
                    formatSelection: function (item, $container) {
                        FormBuilder.buildForm('pim-grid-view-selector-current').then(function (form) {
                            form.setParent(this);
                            return form.configure(item).then(function () {
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
                            var viewFetcher = 'datagrid-' + this.currentViewType;

                            FetcherRegistry.getFetcher(viewFetcher).search(searchParameters).then(function (views) {
                                var choices = this.toSelect2Format(views);

                                if (page === 1 && !options.term) {
                                    choices = this.ensureDefaultView(choices);
                                }

                                options.callback({
                                    results: choices,
                                    more: choices.length >= this.resultsPerPage,
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
                        this.initializeSelection().then(function (view) {
                            callback(view);
                        });
                    }.bind(this)
                };

                this.$select2Instance = initSelect2.init($select, options);

                // Select2 catches ALL events when user clicks on an element in the dropdown list.
                // This method bypasses it to allow to click on sub-elements such as buttons, link...
                var select2 = this.$select2Instance.data('select2');
                select2.onSelect = (function (fn) {
                    return function (data, options) {
                        var target = null;

                        if (options !== null) {
                            target = $(options.target);
                        }

                        // If we clicked on something else than the line (eg. a button), we don't capture the event
                        if (null === target || target.hasClass('grid-view-selector-line-overlay')) {
                            return fn.apply(this, arguments);
                        }
                    };
                })(select2.onSelect);

                this.$select2Instance.on('select2-selecting', function (event) {
                    var view = event.object;
                    this.selectView(view);
                }.bind(this));

                var $menu = this.$('.select2-drop');
                var $search = this.$('.select2-search');

                $search.prepend($('<i class="icon-search"></i>'));

                // If more than 1 view type, we display the view type switcher module
                if (this.viewTypes.length > 1) {
                    var typeSwitcher = new ViewSelectorTypeSwitcher(this.viewTypes);
                    $search.append(typeSwitcher.render().$el);

                    typeSwitcher.listenTo(typeSwitcher, 'grid:view-selector:switch-type', this.switchViewType.bind(this));
                    typeSwitcher.setCurrentViewType(this.currentViewType);

                    $search.find('.select2-input').addClass('with-dropdown');
                }

                FormBuilder.buildForm('pim-grid-view-selector-footer').then(function (form) {
                    form.setParent(this);
                    form.configure().then(function () {
                        $menu.append(form.render().$el);
                    });
                }.bind(this));
            },

            /**
             * Method called on view type switching (triggered by the Type Switcher module).
             * We need to re-trigger the select2 search event to fetch new views.
             *
             * @param {string} selectedType
             */
            switchViewType: function (selectedType) {
                this.currentViewType = selectedType;

                // Force the trigger of the search of select2
                var searchTerm = this.$select2Instance.data('select2').search.val();
                this.$select2Instance.select2('search', '');
                this.$select2Instance.select2('search', searchTerm);
            },

            /**
             * Initialize the Select2 selection based on the DatagridState.
             * Could be the User default one, or an existing view edited or whatever.
             *
             * @returns {Promise}
             */
            initializeSelection: function () {
                var activeViewId = DatagridState.get(this.gridAlias, 'view');
                var userDefaultView = this.defaultUserView;
                var deferred = $.Deferred();

                if (activeViewId) {
                    if ('0' === activeViewId) {
                        deferred.resolve(this.getDefaultView());
                    } else {
                        FetcherRegistry.getFetcher('datagrid-view').fetch(activeViewId, {alias: this.gridAlias})
                            .then(function (view) {
                                view.text = view.label;
                                deferred.resolve(view);
                            }.bind(this));
                    }
                } else if (userDefaultView) {
                    userDefaultView.text = userDefaultView.label;
                    deferred.resolve(userDefaultView);
                } else {
                    deferred.resolve(this.getDefaultView());
                }

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
             * Return the default view object which contains default columns & no filter.
             *
             * @returns {Object}
             */
            getDefaultView: function () {
                return {
                    id: 0,
                    text: __('grid.view_selector.default_view'),
                    columns: this.defaultColumns,
                    filters: '',
                    type: 'view'
                };
            },

            /**
             * Ensure given choices contain a default view if user doesn't have one.
             *
             * @param {array} choices
             *
             * @return {array}
             */
            ensureDefaultView: function (choices) {
                if (null !== this.defaultUserView || this.currentViewType !== 'view') {
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
                if (null !== this.$select2Instance) {
                    this.$select2Instance.select2('close');
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
                        limit: this.resultsPerPage,
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
             * @returns {array}
             */
            toSelect2Format: function (data) {
                return _.map(data, function (view) {
                    view.text = view.label;

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
            }
        });
    }
);

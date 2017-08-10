define(
    [
        'underscore',
        'jquery',
        'pim-router',
        'oro/datagrid-builder',
        'oro/pageable-collection',
        'pim/datagrid/state',
        'oro/datafilter/product_category-filter',
        'require-context',
        'pim/form',
        'pim/user-context'
    ],
    function (
        _,
        $,
        Routing,
        datagridBuilder,
        PageableCollection,
        DatagridState,
        CategoryFilter,
        requireContext,
        BaseForm,
        UserContext
    ) {
        // Copied from macros renderStatefulGrid
        // @TODO - Totally rewrite with proper functions
        return BaseForm.extend({
            config: {},

            initialize(options) {
                this.config = options.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            render() {
                // Put this stuff in initialize
                const { localeParamName } = this.config;
                const locale = UserContext.get('catalogLocale');
                const gridName = this.config.gridName;
                const root = this.getRoot();

                var defaultColumnsRoute = Routing.generate(
                    'pim_datagrid_view_rest_default_columns',
                    {
                        alias: gridName
                    }
                );

                $.get(defaultColumnsRoute, function (defaultColumns) {
                    initDatagrid(defaultColumns);
                });

                var initDatagrid = function (defaultColumns) {
                    var urlParams    = {[ localeParamName ]: locale};
                    urlParams.alias  = gridName;
                    urlParams.params = {[ localeParamName ]: locale};

                    var viewStored = DatagridState.get(gridName, ['view']);
                    if (!viewStored.view) {
                        DatagridState.refreshFiltersFromUrl(gridName);
                    }

                    var hasDefaultView = false;
                    var state          = DatagridState.get(gridName, ['view', 'filters', 'columns']);

                    var applyView = function (viewId) {
                        urlParams[`${gridName}[_parameters][view][id]`] = viewId;

                        DatagridState.set(gridName, { view: viewId });
                    };

                    var applyFilters = function (rawFilters) {
                        var filters = PageableCollection.prototype.decodeStateData(rawFilters);
                        var options = {};

                        if (!_.isEmpty(filters.filters)) {
                            options = {
                                state: {
                                    filters: _.omit(filters.filters, 'scope')
                                }
                            };
                        }

                        var collection = new PageableCollection(null, options);
                        collection.processFiltersParams(urlParams, filters, `${gridName}[_filter]`);

                        for (var column in filters.sorters) {
                            urlParams[`${gridName}[_sort_by][' + column + ']`] =
                                1 === parseInt(filters.sorters[column]) ?
                                        'DESC' :
                                        'ASC';
                        }

                        if (undefined !== filters.pageSize) {
                            urlParams[`${gridName}[_pager][_per_page]`] = filters.pageSize;
                        }

                        if (undefined !== filters.currentPage) {
                            urlParams[`${gridName}[_pager][_page]`] = filters.currentPage;
                        }

                        DatagridState.set(gridName, {
                            filters: rawFilters
                        });
                    };

                    var applyColumns = function (columns) {
                        if (_.isArray(columns)) {
                            columns = columns.join();
                        }
                        urlParams[`${gridName}[_parameters][view][columns]`] = columns;

                        DatagridState.set(gridName, {
                            columns: columns
                        });
                    };

                    if (hasDefaultView && ('0' === state.view || null === state.view)) {
                    } else {
                        if (state.view) {
                            applyView(state.view);
                        }

                        if (state.filters) {
                            applyFilters(state.filters);
                        }

                        if (state.columns) {
                            applyColumns(state.columns);
                        } else {
                            applyColumns(defaultColumns);
                        }
                    }

                    root.trigger('datagrid:getParams', urlParams);

                    state = DatagridState.get(gridName, ['view', 'filters', 'columns']);

                    $.get(Routing.generate('pim_datagrid_load', urlParams), function(resp) {
                        if (state.columns) {
                            resp.metadata.state.parameters = _.extend({}, resp.metadata.state.parameters, {
                                view: {
                                    columns: state.columns,
                                    id: state.view
                                }
                            });
                        }

                        $(`#grid-${gridName}`).data({ 'metadata': resp.metadata, 'data': JSON.parse(resp.data) });

                        var modules = resp.metadata.requireJSModules;
                        modules.push('pim/datagrid/state-listener');
                        var resolvedModules = [];
                        _.each(modules, function(module) {
                            var resolvedModule = requireContext(module);
                            resolvedModules.push(resolvedModule);
                        });

                        datagridBuilder(resolvedModules);
                    });
                };
            }

        });
    }
);

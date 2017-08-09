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
        'pim/form'
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
        BaseForm
    ) {

        // @TODO - Totally rewrite with proper functions
        return BaseForm.extend({

            render() {
                var defaultColumnsRoute = Routing.generate(
                    'pim_datagrid_view_rest_default_columns',
                    {alias: 'product-grid' }
                    );

                $.get(defaultColumnsRoute, function (defaultColumns) {
                    initDatagrid(defaultColumns);
                });

                var initDatagrid = function (defaultColumns) {
                    var urlParams    = {'dataLocale':'en_US'};
                    urlParams.alias  = 'product-grid';
                    urlParams.params = {'dataLocale':'en_US'};

                    var viewStored = DatagridState.get('product-grid', ['view']);
                    if (!viewStored.view) {
                        DatagridState.refreshFiltersFromUrl('product-grid');
                    }

                    var hasDefaultView = false;
                    var state          = DatagridState.get('product-grid', ['view', 'filters', 'columns']);

                    var applyView = function (viewId) {
                        urlParams['product-grid[_parameters][view][id]'] = viewId;

                        DatagridState.set('product-grid', {
                            view: viewId
                        });
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
                        collection.processFiltersParams(urlParams, filters, 'product-grid[_filter]');

                        for (var column in filters.sorters) {
                            urlParams['product-grid[_sort_by][' + column + ']'] =
                                1 === parseInt(filters.sorters[column]) ?
                                        'DESC' :
                                        'ASC';
                        }

                        if (undefined !== filters.pageSize) {
                            urlParams['product-grid[_pager][_per_page]'] = filters.pageSize;
                        }

                        if (undefined !== filters.currentPage) {
                            urlParams['product-grid[_pager][_page]'] = filters.currentPage;
                        }

                        DatagridState.set('product-grid', {
                            filters: rawFilters
                        });
                    };

                    var applyColumns = function (columns) {
                        if (_.isArray(columns)) {
                            columns = columns.join();
                        }
                        urlParams['product-grid[_parameters][view][columns]'] = columns;

                        DatagridState.set('product-grid', {
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

                    console.log(urlParams);

                    var categoryFilter = new CategoryFilter(
                    urlParams,
                    'product-grid',
                    'pim_enrich_categorytree',
                    '#tree-old'
                );

                    state = DatagridState.get('product-grid', ['view', 'filters', 'columns']);

                    $.get(Routing.generate('pim_datagrid_load', urlParams), function(resp) {
                        if (state.columns) {
                            resp.metadata.state.parameters = _.extend({}, resp.metadata.state.parameters, {
                                view: {
                                    columns: state.columns,
                                    id: state.view
                                }
                            });
                        }

                        $('#grid-product-grid').data({ 'metadata': resp.metadata, 'data': JSON.parse(resp.data) });

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

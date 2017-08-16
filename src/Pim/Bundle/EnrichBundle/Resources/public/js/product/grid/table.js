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
        'pim/user-context',
        'pim/fetcher-registry'
    ],
    function(
        _,
        $,
        Routing,
        datagridBuilder,
        PageableCollection,
        DatagridState,
        CategoryFilter,
        requireContext,
        BaseForm,
        UserContext,
        FetcherRegistry
    ) {
        return BaseForm.extend({
            config: {},

            initialize(options) {
                this.config = options.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            getDefaultView() {
                return FetcherRegistry.getFetcher('datagrid-view')
                    .defaultUserView('product-grid')
                    .then(defaultUserView => defaultUserView.view);
            },

            getDefaultColumns() {
                return $.get(Routing.generate(
                    this.config.defaultColumnsUrl, {
                        alias: this.config.gridName
                    }
                ));
            },

            loadDataGrid(resp) {
                const { gridName } = this.config;
                const state = DatagridState.get(gridName, ['view', 'filters', 'columns']);

                if (state.columns) {
                    resp.metadata.state.parameters = _.extend({}, resp.metadata.state.parameters, {
                        view: {
                            columns: state.columns,
                            id: state.view
                        }
                    });
                }

                $(`#grid-${gridName}`).data({
                    'metadata': resp.metadata,
                    'data': JSON.parse(resp.data)
                });

                var modules = resp.metadata.requireJSModules;
                modules.push('pim/datagrid/state-listener');

                var resolvedModules = [];
                _.each(modules, function(module) {
                    var resolvedModule = requireContext(module);
                    resolvedModules.push(resolvedModule);
                });

                datagridBuilder(resolvedModules);
            },

            refreshGridFilters() {
                DatagridState.refreshFiltersFromUrl(this.config.gridName);
            },

            getInitialParams() {
                const { localeParamName, gridName } = this.config;
                const locale = UserContext.get('catalogLocale');
                var urlParams = { [localeParamName]: locale, alias: gridName };
                urlParams.params = _.clone(urlParams);

                return urlParams;
            },

            applyColumns(columns, urlParams) {
                const { gridName } = this.config;
                if (_.isArray(columns))  columns = columns.join();

                urlParams[`${gridName}[_parameters][view][columns]`] = columns;

                DatagridState.set(gridName, { columns: columns });

                return urlParams;
            },

            applyView(viewId, urlParams) {
                const { gridName } = this.config;
                urlParams[`${gridName}[_parameters][view][id]`] = viewId;
                DatagridState.set(gridName, {  view: viewId });

                return urlParams;
            },

            applyFilters(rawFilters, urlParams) {
                const { gridName } = this.config;
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

                return urlParams;
            },

            setDatagridState(defaultColumns, defaultView) {
                const { gridName } = this.config;
                const root = this.getRoot();
                var urlParams = this.getInitialParams();

                if (!DatagridState.get(gridName, ['view'])) this.refreshGridFilters();

                var state = DatagridState.get(gridName, ['view', 'filters', 'columns']);

                if (defaultView && ('0' === state.view || null === state.view)) {
                    urlParams = this.applyView(defaultView.id, urlParams);
                    urlParams = this.applyFilters(defaultView.filters, urlParams);
                    urlParams = this.applyColumns(defaultView.columns, urlParams);
                } else {
                    if (state.view) urlParams = this.applyView(state.view, urlParams);
                    if (state.filters) urlParams = this.applyFilters(state.filters, urlParams);

                    if (state.columns) {
                        urlParams = this.applyColumns(state.columns, urlParams);
                    } else {
                        urlParams = this.applyColumns(defaultColumns, urlParams);
                    }
                }

                root.trigger('datagrid:getParams', urlParams);
                $.get(Routing.generate('pim_datagrid_load', urlParams), this.loadDataGrid.bind(this));
            },

            render() {
                $.when(this.getDefaultColumns(), this.getDefaultView())
                .then((defaultColumns, defaultView) => this.setDatagridState(defaultColumns, defaultView));
            }

        });
    }
);
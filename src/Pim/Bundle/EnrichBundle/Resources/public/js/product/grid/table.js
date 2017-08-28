define(
    [
        'underscore',
        'jquery',
        'pim-router',
        'oro/datagrid-builder',
        'oro/pageable-collection',
        'pim/datagrid/state',
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
                    .defaultUserView(this.config.gridName)
                    .then(defaultUserView => defaultUserView.view);
            },

            getDefaultColumns() {
                return FetcherRegistry.getFetcher('datagrid-view')
                    .defaultColumns(this.config.gridName);
            },

            loadDataGrid(resp) {
                const { gridName } = this.config;
                const state = DatagridState.get(gridName, ['view', 'filters', 'columns']);

                if (state.columns) {
                    resp.metadata.state.parameters = _.extend({},
                        resp.metadata.state.parameters,
                        {
                            view: {
                                columns: state.columns,
                                id: state.view
                            }
                        }
                    );
                }

                $(`#grid-${gridName}`).data({
                    'metadata': resp.metadata,
                    'data': JSON.parse(resp.data)
                });

                const modules = resp.metadata.requireJSModules;
                modules.push('pim/datagrid/state-listener');

                const url = decodeURI(resp.metadata.options.url).split('?')[0];
                resp.metadata.options.url = `${url}?dataLocale=${UserContext.get('catalogLocale')}`;

                const resolvedModules = [];

                _.each(modules, function(module) {
                    const resolvedModule = requireContext(module);
                    resolvedModules.push(resolvedModule);
                });

                datagridBuilder(resolvedModules);
            },

            getInitialParams() {
                const { localeParamName, gridName } = this.config;
                const locale = UserContext.get('catalogLocale');
                const urlParams = { [localeParamName]: locale, alias: gridName };
                urlParams.params = {[ localeParamName ]: locale };

                return urlParams;
            },

            applyColumns(columns, urlParams) {
                urlParams = $.extend(true, {}, urlParams);
                const { gridName } = this.config;
                if (_.isArray(columns)) columns = columns.join();

                urlParams[`${gridName}[_parameters][view][columns]`] = columns;

                DatagridState.set(gridName, { columns: columns });

                return urlParams;
            },

            applyView(viewId, urlParams) {
                urlParams = $.extend(true, {}, urlParams);
                const { gridName } = this.config;
                urlParams[`${gridName}[_parameters][view][id]`] = viewId;

                DatagridState.set(gridName, {  view: viewId });

                return urlParams;
            },

            applyFilters(rawFilters, urlParams) {
                urlParams = $.extend(true, {}, urlParams);
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
                    urlParams[`${gridName}[_sort_by][${column}]`] =
                    1 === parseInt(filters.sorters[column]) ?
                    'DESC' :
                    'ASC';
                }

                if (filters.pageSize) {
                    urlParams[`${gridName}[_pager][_per_page]`] = filters.pageSize;
                }

                if (filters.currentPage) {
                    urlParams[`${gridName}[_pager][_page]`] = filters.currentPage;
                }

                DatagridState.set(gridName, {
                    filters: rawFilters
                });

                return urlParams;
            },

            setDatagridState(defaultColumns, defaultView) {
                const { gridName, datagridLoadUrl} = this.config;
                let params = this.getInitialParams();

                if (!DatagridState.get(gridName, ['view'])) {
                    DatagridState.refreshFiltersFromUrl(gridName);
                }

                const state = DatagridState.get(gridName, ['view', 'filters', 'columns']);

                if (defaultView && ('0' === state.view || null === state.view)) {
                    params = this.applyView(defaultView.id, params);
                    params = this.applyFilters(defaultView.filters, params);
                    params = this.applyColumns(defaultView.columns, params);
                } else {
                    if (state.view) params = this.applyView(state.view, params);
                    if (state.filters) params = this.applyFilters(state.filters, params);
                    params = this.applyColumns(state.columns || defaultColumns, params);
                }

                this.getRoot().trigger('datagrid:getParams', params);

                return $.get(
                    Routing.generate(datagridLoadUrl, params),
                    this.loadDataGrid.bind(this)
                );
            },

            render() {
                $.when(this.getDefaultColumns(), this.getDefaultView())
                .then((defaultColumns, defaultView) => this.setDatagridState(defaultColumns, defaultView));
            }
        });
    }
);

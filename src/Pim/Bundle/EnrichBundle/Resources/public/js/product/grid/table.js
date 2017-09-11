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
        'pim/fetcher-registry',
        'pim/datagrid/state-listener'
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
        FetcherRegistry,
        StateListener
    ) {
        return BaseForm.extend({
            config: {},

            /**
             * @inheritdoc
             */
            initialize(options) {
                this.config = options.config;

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * Fetch default view for grid
             * @return {Promise}
             */
            getDefaultView() {
                return FetcherRegistry.getFetcher('datagrid-view')
                    .defaultUserView(this.config.gridName)
                    .then(defaultUserView => defaultUserView.view);
            },

            /**
             * Fetch default columns for grid
             * @return {Promise}
             */
            getDefaultColumns() {
                return FetcherRegistry.getFetcher('datagrid-view')
                    .defaultColumns(this.config.gridName);
            },

            /**
             * Build the datagrid
             * @param  {Object} resp Datagrid load response
             */
            loadDataGrid(resp) {
                if (typeof resp === 'string' || null === resp) {
                    return;
                }

                const { gridName } = this.config;
                const dataLocale = UserContext.get('catalogLocale');
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
                    metadata: resp.metadata,
                    data: JSON.parse(resp.data)
                });

                const url = decodeURI(resp.metadata.options.url).split('?')[0];
                const localeParam = $.param({ dataLocale });
                resp.metadata.options.url =  `${url}?${localeParam}`;

                datagridBuilder([ StateListener ]);
            },

            /**
             * Get the initial grid params with locale
             * @return {Object} urlParams
             */
            getInitialParams() {
                const dataLocale = UserContext.get('catalogLocale');
                const alias = this.config.gridName;
                const urlParams = { dataLocale, alias };
                urlParams.params = { dataLocale };

                return urlParams;
            },

            /**
             * Set the columns on the datagrid state
             * @param  {Array} columns   An array of columns
             * @param  {Object} urlParams Url params
             * @return {Object}
             */
            applyColumns(columns, urlParams) {
                urlParams = _.clone(urlParams);
                const { gridName } = this.config;
                if (_.isArray(columns)) columns = columns.join();

                urlParams[`${gridName}[_parameters][view][columns]`] = columns;
                DatagridState.set(gridName, { columns: columns });

                return urlParams;
            },

            /**
             * Set the selected view on the datagrid state
             * @param  {String} viewId    The id of the view
             * @param  {Object} urlParams Url params
             * @return {Object}
             */
            applyView(viewId, urlParams) {
                urlParams = _.clone(urlParams);
                const { gridName } = this.config;

                urlParams[`${gridName}[_parameters][view][id]`] = viewId;
                DatagridState.set(gridName, { view: viewId });

                return urlParams;
            },

            /**
             * Apply filters to the datagrid params
             * @param  {String} rawFilters Filters as string
             * @param  {Object} urlParams  Url params
             * @return {Object}
             */
            applyFilters(rawFilters, urlParams) {
                urlParams = _.clone(urlParams);
                const { gridName } = this.config;
                let filters = PageableCollection.prototype.decodeStateData(rawFilters);
                let options = {};

                if (!_.isEmpty(filters.filters)) {
                    options = {
                        state: {
                            filters: _.omit(filters.filters, 'scope')
                        }
                    };
                }

                let collection = new PageableCollection(null, options);
                collection.processFiltersParams(urlParams, filters, `${gridName}[_filter]`);

                for (let column in filters.sorters) {
                    urlParams[`${gridName}[_sort_by][${column}]`] =
                    1 === parseInt(filters.sorters[column]) ?
                    'DESC' :
                    'ASC';
                }

                if (filters.pageSize) {
                    urlParams[`${gridName}[_pager][_per_page]`] = 100;
                }

                if (filters.currentPage) {
                    urlParams[`${gridName}[_pager][_page]`] = filters.currentPage;
                }

                DatagridState.set(gridName, { filters: rawFilters });

                return urlParams;
            },

            /**
             * Apply filters columns and view for the datagrid
             * @param {Array} defaultColumns
             * @param {String} defaultView
             */
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

            /**
             * @inheritdoc
             */
            render() {
                $.when(this.getDefaultColumns(), this.getDefaultView())
                .then((defaultColumns, defaultView) => {
                    return this.setDatagridState(defaultColumns, defaultView);
                });
            }
        });
    }
);

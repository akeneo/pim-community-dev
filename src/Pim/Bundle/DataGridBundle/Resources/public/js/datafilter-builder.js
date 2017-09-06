define(
    [
        'jquery',
        'underscore',
        'oro/tools',
        'oro/mediator',
        'oro/datafilter/collection-filters-manager',
        'pim/form'
    ],
    function(
        $,
        _,
        tools,
        mediator,
        FiltersManager,
        BaseForm
     ) {
        const DataFilterBuilder = BaseForm.extend({
            initialized: false,
            config: {
                filterModuleName: 'oro/datafilter/{{type}}-filter',
                filterTypes: {
                    string: 'choice',
                    choice: 'select',
                    selectrow: 'select-row',
                    multichoice: 'multiselect',
                    boolean: 'select'
                }
            },

            /**
             * {@inheritdoc}
             */
            initialize(options = {}) {
                this.config = Object.assign(this.config, options.config || {});

                mediator.once('datagrid_collection_set_after', this.initHandler.bind(this));
                mediator.once('hash_navigation_request:start', function() {
                    if (!this.initialized) {
                        mediator.off('datagrid_collection_set_after', this.initHandler.bind(this));
                    }
                });

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * Sets the element and collection, starts the builder
             * @param  {Object} collection Data collection
             * @param  {Object} $el        Element
             */
            initHandler(collection, $el) {
                this.collection = collection;
                this.$el = $el;
                this.initBuilder();
                this.initialized = true;
            },

            /**
             * Collect and load the filter modules
             */
            initBuilder() {
                this.metadata = Object.assign({
                    filters: {},
                    options: {}
                }, this.$el.data('metadata'));

                this.modules = {};
                this.collectModules.call(this);
                tools.loadModules(this.modules, this.build.bind(this));
            },

            /**
             * Collects required modules
             */
            collectModules() {
                var modules = this.modules;
                _.each(this.metadata.filters, filter => {
                    var type = filter.type;
                    modules[type] = this.config.filterModuleName.replace(
                        '{{type}}',
                        this.config.filterTypes[type] || type
                    );
                });
            },

            /**
             * Renders the filters
             */
            build: function () {
                var options = this.combineOptions.call(this);
                options.collection = this.collection;
                options.displayManageFilters = _.result(this.metadata.options, 'manageFilters', true);
                options.filtersAsColumn = _.result(this.metadata.options, 'filtersAsColumn', false);
                var filtersList = new FiltersManager(options);
                this.$el.prepend(filtersList.render().$el);

                mediator.trigger('datagrid_filters:rendered', this.collection);

                if (this.collection.length === 0) {
                    filtersList.$el.hide();
                }
                mediator.trigger('datagrid_filters:build.post', filtersList);
            },

            /**
             * Process metadata and combines options for filters
             *
             * @returns {Object}
             */
            combineOptions() {
                const filters = {};
                const modules = this.modules;
                const collection = this.collection;

                _.each(this.metadata.filters, function(options) {
                    if (_.has(options, 'name') && _.has(options, 'type')) {
                        // @TODO pass collection only for specific filters
                        if (options.type === 'selectrow') {
                            options.collection = collection;
                        }
                        filters[options.name] = new(modules[options.type].extend(options))(options);
                    }
                });

                return { filters };
            }
        });

        // Drop in TIP-733-2
        DataFilterBuilder.init = () => new DataFilterBuilder();

        return DataFilterBuilder;
    });

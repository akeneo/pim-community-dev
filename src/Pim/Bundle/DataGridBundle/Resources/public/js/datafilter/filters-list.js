define(['underscore', 'pim/form', 'oro/mediator', 'oro/tools'],
    function (_, BaseForm, mediator, tools) {

        return BaseForm.extend({
            options: {},
            filters: [],
            className: 'AknFilterBox--list',

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

            initialize(options) {
                this.options = options.config;

                mediator.once('datagrid_collection_set_after', (collection, gridElement) => {
                    this.collection = collection;
                    this.gridElement = gridElement;
                    this.metadata = this.gridElement.data('metadata') || {};
                    this.filters = this.metadata.filters;

                    this.modules = {};
                    this.collectModules.call(this);

                    tools.loadModules(this.modules, () => {
                        const options = this.combineOptions.call(this);
                        options.collection = this.collection;
                        options.displayManageFilters = _.result(this.metadata.options, 'manageFilters', true);

                        this.filters = options.filters;
                        this.render();

                        mediator.trigger('datagrid_filters:loaded', options);
                        mediator.trigger('datagrid_filters:rendered', this.collection, this.filters);
                    });
                });

                BaseForm.prototype.initialize.apply(this, arguments);
            },

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
            },

            collectModules() {
                var modules = this.modules;
                _.each(this.filters, filter => {
                    var type = filter.type;
                    modules[type] = this.config.filterModuleName.replace(
                        '{{type}}',
                        this.config.filterTypes[type] || type
                    );
                });
            },

            render() {
                _.each(this.filters, function (filter) {
                    if (!filter.enabled) {
                        filter.hide();
                    }
                    if (filter.enabled) {
                        filter.render();
                    }
                    if (filter.$el.length > 0) {
                        this.$el.append(filter.$el.get(0));
                    }
                }, this);
            }
        });
    }
);


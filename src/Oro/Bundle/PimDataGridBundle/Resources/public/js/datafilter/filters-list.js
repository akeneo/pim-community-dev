define(['underscore', 'pim/form', 'oro/mediator', 'oro/tools'],
    function (_, BaseForm, mediator, tools) {

        return BaseForm.extend({
            options: {},
            filters: [],
            isLoaded: false,
            className: 'AknFilterBox-list filter-box',

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
             * @inheritdoc
             */
            initialize(options) {
                this.options = options.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(mediator, 'datagrid_collection_set_after', this.loadFilterModules.bind(this));

                BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Load the filter modules given a datagrid collection and grid metadata
             * @param  {Object} collection  Backbone collection for a datagrid
             * @param  {HTMLElement} gridElement Grid element
             */
            loadFilterModules(collection, gridElement) {
                if (this.isLoaded) {
                    return;
                }
                this.isLoaded = true;
                this.collection = collection;
                this.gridElement = gridElement;
                this.metadata = this.gridElement.data('metadata') || {};
                this.filters = this.metadata.filters;
                this.modules = this.collectModules();

                tools.loadModules(this.modules, () => {
                    const options = this.combineOptions.call(this);
                    options.collection = this.collection;
                    options.displayManageFilters = _.result(this.metadata.options, 'manageFilters', true);

                    this.filters = options.filters || [];
                    this.render();

                    mediator.trigger('datagrid_filters:loaded', options);
                    mediator.trigger('datagrid_filters:rendered', this.collection, this.filters);
                });
            },

            /**
             * Returns the filter module names
             */
            collectModules() {
                const modules = {};

                _.each(this.filters, filter => {
                    const type = filter.type;
                    modules[type] = this.config.filterModuleName.replace(
                        '{{type}}',
                        this.config.filterTypes[type] || type
                    );
                });

                return modules;
            },

            /**
             * Passes data to and creates new instances of filter modules
             * @return {Object} The filter modules
             */
            combineOptions() {
                const filters = {};

                _.each(this.metadata.filters, options => {
                    if (_.has(options, 'name') && _.has(options, 'type')) {
                        if (options.type === 'selectrow') {
                            options.collection = this.collection;
                        }
                        filters[options.name] = new(this.modules[options.type].extend(options))(options);
                    }
                });

                return { filters };
            },

            /**
             * Render filters
             */
            render() {
                _.each(this.filters, function (filter) {
                    if (!filter.enabled) {
                        filter.hide();
                    }
                    if (filter.enabled) {
                        filter.render();
                    }
                    if (filter.$el.length > 0) {
                        if (filter.isSearch && (this.options.displayedAsColumn === true)) {
                            this.getRoot().$('.search-zone').append(filter.$el.get(0));
                        } else {
                            this.$el.append(filter.$el.get(0));
                        }
                    }
                }, this);

                BaseForm.prototype.render.apply(this, arguments);
            }
        });
    }
);


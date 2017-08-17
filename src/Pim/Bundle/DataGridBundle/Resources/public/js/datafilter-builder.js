define(['jquery', 'underscore', 'oro/tools', 'oro/mediator', 'oro/datafilter/collection-filters-manager', 'pim/form'],
    function($, _, tools, mediator, FiltersManager, BaseForm) {
        'use strict';

        const DataFilterBuilder = BaseForm.extend({
            initialized: false,
            filterModuleName: 'oro/datafilter/{{type}}-filter',
            filterTypes: {
                string: 'choice',
                choice: 'select',
                selectrow: 'select-row',
                multichoice: 'multiselect',
                boolean: 'select'
            },

            initialize() {
                mediator.once('datagrid_collection_set_after', this.initHandler.bind(this));
                mediator.once('hash_navigation_request:start', function() {
                    if (!this.initialized) {
                        mediator.off('datagrid_collection_set_after', this.initHandler.bind(this));
                    }
                });

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            initHandler: function(collection, $el) {
                this.collection = collection;
                this.$el = $el;
                this.initBuilder();
                this.initialized = true;
            },

            initBuilder: function() {
                this.metadata = _.extend({
                    filters: {},
                    options: {}
                }, this.$el.data('metadata'));
                this.modules = {};
                this.collectModules.call(this);
                tools.loadModules(this.modules, _.bind(this.build, this));
            },

            /**
             * Collects required modules
             */
            collectModules: function() {
                var modules = this.modules;
                _.each((this.metadata.filters || {}), (filter) => {
                    var type = filter.type;
                    modules[type] = this.filterModuleName.replace('{{type}}', this.filterTypes[type] || type);
                });
            },

            build: function() {
                var displayManageFilters = _.result(this.metadata.options, 'manageFilters', true);
                var options = this.combineOptions.call(this);
                options.collection = this.collection;
                options.displayManageFilters = displayManageFilters;
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
            combineOptions: function() {
                var filters = {},
                    modules = this.modules,
                    collection = this.collection;
                _.each(this.metadata.filters, function(options) {
                    if (_.has(options, 'name') && _.has(options, 'type')) {
                        // @TODO pass collection only for specific filters
                        if (options.type == 'selectrow') {
                            options.collection = collection
                        }
                        filters[options.name] = new(modules[options.type].extend(options))(options);
                    }
                });

                return {
                    filters: filters
                };
            }
        });

        // This is for the grids that don't yet use form extensions
        DataFilterBuilder.init = () => new DataFilterBuilder();

        return DataFilterBuilder;
    });

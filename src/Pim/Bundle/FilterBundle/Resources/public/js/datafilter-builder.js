
/* jshint browser:true */
/* global define, require */
define(['underscore', 'oro/tools', 'oro/mediator', 'oro/datafilter/collection-filters-manager'],
function(_, tools,  mediator, FiltersManager) {
    'use strict';

    var initialized = false,
        filterModuleName = 'oro/datafilter/{{type}}-filter',
        filterTypes = {
            date:                 'date',
            string:               'choice',
            choice:               'select',
            selectrow:            'select-row',
            multichoice:          'multiselect',
            metric:               'metric',
            boolean:              'select',
            product_category:     'product_category',
            product_scope:        'product_scope',
            product_completeness: 'product_completeness',
            'ajax-choice':        'ajax-choice',
            price:                'price',
            number:               'number'
        },
        methods = {
            initBuilder: function () {
                this.metadata = _.extend({filters: {}}, this.$el.data('metadata'));
                this.modules = {};
                methods.collectModules.call(this);
                tools.loadModules(this.modules, _.bind(methods.build, this));
            },

            /**
             * Collects required modules
             */
            collectModules: function () {
                var modules = this.modules;
                _.each(filterTypes, function (type, filterType) {
                     modules[filterType] = filterModuleName.replace('{{type}}', type);
                });
            },

            build: function () {
                var options = methods.combineOptions.call(this);
                options.collection = this.collection;
                options.metadataUrl = this.metadata.options.metadataUrl;
                var filtersList = new FiltersManager(options);
                this.$el.prepend(filtersList.render().$el);
                mediator.trigger('datagrid_filters:rendered', this.collection);
                if (this.collection.length === 0) {
                    filtersList.$el.hide();
                }
            },

            /**
             * Process metadata and combines options for filters
             *
             * @returns {Object}
             */
            combineOptions: function () {
                var filters= {},
                    modules = this.modules,
                    collection = this.collection,
                    createFilter = function(options) {
                        if (!(options.type in modules)) {
                            modules[options.type] = filterModuleName.replace('{{type}}', filterTypes[options.type] || options.type);
                        }
                        if (options.type === 'selectrow') {
                            options.collection = collection;
                        }
                        return new (modules[options.type].extend(options));
                    };
                _.each(this.metadata.filters, function (options) {
                    if (_.has(options, 'name') && _.has(options, 'type')) {
                        filters[options.name] = createFilter(options);
                    }
                });
                return {filters: filters, callback: createFilter};
            }
        },
        initHandler = function (collection, $el) {
            methods.initBuilder.call({$el: $el, collection: collection});
            initialized = true;
        };

    return {
        init: function () {
            initialized = false;

            mediator.once('datagrid_collection_set_after', initHandler);
            mediator.once('hash_navigation_request:start', function() {
                if (!initialized) {
                    mediator.off('datagrid_collection_set_after', initHandler);
                }
            });
        }
    };
});

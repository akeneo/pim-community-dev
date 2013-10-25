/* jshint browser:true */
/* global define, require */
define(['jquery', 'underscore', 'backbone', 'oro/translator', 'oro/mediator', 'oro/registry', 'oro/loading-mask',
    'oro/pageable-collection', 'oro/datagrid/filter-list', 'oro/datagrid/grid', 'oro/datagrid/router',
    'oro/datagrid/grid-views/view'],
function($, _, Backbone, __, mediator, registry, LoadingMask,
         PageableCollection, FilterList, Grid, GridRouter, GridViewsView) {
    'use strict';

    var gridSelector = '[data-type="datagrid"]:not([data-rendered])',
        datagridGridViewsSelector = '.page-title > .navbar-extra .span9:last',
        cells = {
            date:     'oro/datagrid/moment-cell',
            datetime: 'oro/datagrid/moment-cell',
            decimal:  'oro/datagrid/number-cell',
            integer:  'oro/datagrid/integer-cell',
            boolean:  'oro/datagrid/boolean-cell',
            html:     'oro/datagrid/html-cell',
            options:  'oro/datagrid/select-cell',
            string:   'oro/datagrid/string-cell',
            percent:  'oro/datagrid/percent-cell'
        },
        filters = {
            text:        'oro/datafilter/choice-filter',
            number:      'oro/datafilter/number-filter',
            date:        'oro/datafilter/date-filter',
            datetime:    'oro/datafilter/datetime-filter',
            select:      'oro/datafilter/select-filter',
            choice:      'oro/datafilter/select-filter',
            selectrow:   'oro/datafilter/select-row-filter',
            multiselect: 'oro/datafilter/multiselect-filter',
            multichoice: 'oro/datafilter/multiselect-filter',
            boolean:     'oro/datafilter/select-filter'
        },
        actions = {
            navigate: 'oro/datagrid/navigate-action',
            'delete': 'oro/datagrid/delete-action',
            ajax:     'oro/datagrid/ajax-action',
            mass:     'oro/datagrid/mass-action'
        },

        helpers = {
            capitalize: function (s) {
                return s[0].toUpperCase() + s.slice(1);
            },
            cellType: function (type) {
                return this.capitalize(type) + 'Cell';
            },
            actionType: function (type) {
                return this.capitalize(type) + 'Acton';
            },
            filterType: function (type) {
                return this.capitalize(type) + 'Filter';
            }
        },

        methods = {
            /**
             * Reads data from grid container, collects required modules and runs grid builder
             */
            initBuilder: function () {
                this.data = this.$el.data('data');
                this.metadata = this.$el.data('metadata');
                this.modules = {};
                methods.collectModules.call(this);
                var modules = this.modules,
                    requirements = _.values(this.modules),
                    buildGrid = _.bind(methods.buildGrid, this);
                // load all dependencies and build grid
                require(requirements, function () {
                    _.each(modules, _.bind(function(value, key) {
                        modules[key] = this[value];
                    }, _.object(requirements, _.toArray(arguments))));
                    buildGrid();
                });
            },

            /**
             * Collects required modules
             */
            collectModules: function () {
                var modules = this.modules;
                // cells
                _.each(this.metadata.columns || {}, function (column) {
                    if (_.isUndefined(cells[column.type])) {
                        throw new ReferenceError('Undefined module for cell of column type "'+ column.type + '"');
                    }
                    modules[helpers.cellType(column.type)] = cells[column.type];
                });
                // filters
                _.each((this.metadata.filters || {list: {}}).list || {}, function (filter) {
                    if (_.isUndefined(filters[filter.type])) {
                        throw new ReferenceError('Undefined module for filter of type "'+ filter.type + '"');
                    }
                    modules[helpers.filterType(filter.type)] = filters[filter.type];
                });
                // actions
                _.each(actions, function (path, type) {
                    modules[helpers.actionType(type)] = path;
                });
            },

            /**
             * Build grid
             */
            buildGrid: function () {
                var options, collection, grid,
                    gridName = this.metadata.options.gridName;

                // @todo make a template configurable
                this.$el.append(
                    '<div class="clearfix" data-type="filter"></div>' +
                    '<div class="clearfix" data-type="grid"></div>'
                );

                // create collection
                try {
                    options = methods.combineCollectionOptions.call(this);
                } catch (e) {
                    // @todo  handle exception
                }
                console.log(options);
                collection = new PageableCollection(this.data, options);
                mediator.trigger("datagrid_collection_set_after", collection);

                // create grid
                try {
                    options = methods.combineGridOptions.call(this);
                } catch (e) {
                    // @todo  handle exception
                }
                // @todo add placeholder for messages
                options.noDataHint = __("No user exists.");
                options.noResultsHint = __("No user was found to match your search. Try modifying your search criteria ...");
                options.collection = collection;
                options.loadingMask = LoadingMask.extend({loadingHint: __("Loading...")});
                grid = new Grid(options);
                this.$el.find('[data-type=grid]').append(grid.render().$el);
                registry.setElement('datagrid', gridName, grid);
                mediator.trigger("datagrid:created:" + gridName, grid);

                // create filters
                try {
                    //options = methods.combineFilterOptions.call(this);
                    // @todo there's something wrong, filters should not require all grid's options
                    options = _.extend({
                        addButtonHint: __("Manage filters"),
                        collection: collection
                    }, options);
                } catch (e) {
                    // @todo  handle exception
                }
                this.$el.find('[data-type=filters]').append((new FilterList(options)).render().$el);
                mediator.trigger("datagrid_filters:rendered", collection);

                // create grid view
                $(datagridGridViewsSelector).append((new GridViewsView({collection: collection})).render().$el);

                // register router
                new GridRouter({collection: collection});

                // @todo why do we need start history here?
                if (!Backbone.History.started) {
                    Backbone.history.start();
                }
            },

            /**
             * Process metadata and combines options for collection
             *
             * @returns {Object}
             */
            combineCollectionOptions: function() {
                return _.extend({
                    inputName: this.metadata.options.gridName,
                    parse: true,
                    state: {
                        filters: {},
                        sorters: _.extend({}, this.metadata.sorter.state),
                        gridView: {}
                    }
                }, this.metadata.options);
            },

            /**
             * Process metadata and combines options for datagrid
             *
             * @returns {Object}
             */
            combineGridOptions: function() {
                var columns, filters = {},
                    rowActions = {},
                    massActions = {},
                    toolbarOptions = {},
                    modules = this.modules,
                    metadata = this.metadata;

                // columns
                columns = _.map(this.metadata.columns, function(cell) {
                    var optionKeys = ['name', 'label', 'renderable', 'editable'],
                        options = _.pick.apply(null, [cell].concat(optionKeys)),
                        cellOptions = _.omit.apply(null, [cell].concat(optionKeys.concat('type'))),
                        cellType = modules[helpers.cellType(cell.type)];
                    if (!_.isEmpty(cellOptions)) {
                        cellType = cellType.extend(cellOptions);
                    }
                    options.cell = cellType;
                    options.sortable = _.contains(metadata.sorter.columns || [], options.name);
                    return options;
                });

                // @todo process filters, row and mass actions + toolbar options

                return {
                    name: this.metadata.options.gridName,
                    columns: columns,
                    filters: filters,
                    rowActions: rowActions,
                    massActions: massActions,
                    toolbarOptions: toolbarOptions,
                    multipleSorting: this.metadata.sorter.options.multiple_sorting || false,
                    entityHint: "User"
                };
            },

            /**
             * Process metadata and combines options for filters
             *
             * @returns {Object}
             */
            combineFilterOptions: function () {
                return {};
            }
        };


    /**
     * Process datagirid's metadata and creates datagrid
     *
     * @export oro/datagrid-builder
     * @name   oro.datagridBuilder
     */
    return function (el) {
        var $container = $(el || document),
            $grids = ($container.is(gridSelector) && $container) || $container.find(gridSelector);
        $grids.each(function (i, el) {
            methods.initBuilder.call({$el: $(el)});
        }).data('rendered', true);
    };
});

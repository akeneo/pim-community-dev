/* jshint browser:true */
/* global define, require */
define(['jquery', 'underscore', 'backbone', 'oro/translator', 'oro/tools', 'oro/mediator', 'oro/registry', 'oro/loading-mask',
    'oro/pageable-collection', 'oro/datagrid/grid', 'oro/datagrid/router',
    'oro/datagrid/grid-views/view'],
function($, _, Backbone, __, tools, mediator, registry, LoadingMask,
         PageableCollection, Grid, GridRouter, GridViewsView) {
    'use strict';

    var gridSelector = '[data-type="datagrid"]:not([data-rendered])',
        gridGridViewsSelector = '.page-title > .navbar-extra .span9:last',
        cellModuleName = 'oro/datagrid/{{type}}-cell',
        actionModuleName = 'oro/datagrid/{{type}}-action',
        cellTypes = {
            date:     'moment',
            datetime: 'moment',
            decimal:  'number'
        },

        helpers = {
            cellType: function (type) {
                return type + 'Cell';
            },
            actionType: function (type) {
                return type + 'Acton';
            }
        },

        methods = {
            /**
             * Reads data from grid container, collects required modules and runs grid builder
             */
            initBuilder: function () {
                this.metadata = _.extend({
                    columns: [],
                    options: {},
                    state: {},
                    rowActions: {},
                    massActions: {}
                }, this.$el.data('metadata'));
                this.modules = {};
                methods.collectModules.call(this);
                // load all dependencies and build grid
                tools.loadModules(this.modules, _.bind(methods.buildGrid, this));
            },

            /**
             * Collects required modules
             */
            collectModules: function () {
                var modules = this.modules,
                    metadata = this.metadata,
                    moduleName = function (template, type) {
                        return template.replace('{{type}}', type);
                    };
                // cells
                _.each(metadata.columns, function (column) {
                    var type = column.type;
                    modules[helpers.cellType(type)] = moduleName(cellModuleName, cellTypes[type] || type);
                });
                // actions
                _.each(_.values(metadata.rowActions).concat(_.values(metadata.massActions)), function (action) {
                    modules[helpers.actionType(action.type)] = moduleName(actionModuleName, action.type);
                });
            },

            /**
             * Build grid
             */
            buildGrid: function () {
                var options, collection, grid;

                // create collection
                options = methods.combineCollectionOptions.call(this);
                collection = new PageableCollection(this.$el.data('data'), options);
                mediator.trigger('datagrid_collection_set_after', collection, this.$el);

                // create grid
                options = methods.combineGridOptions.call(this);
                grid = new Grid(_.extend({collection: collection}, options));
                this.$el.append(grid.render().$el);
                registry.setElement('datagrid', options.name, grid);
                mediator.trigger('datagrid:created:' + options.name, grid);

                // create grid view
                $(gridGridViewsSelector).append((new GridViewsView({collection: collection})).render().$el);

                // register router
                new GridRouter({collection: collection});
            },

            /**
             * Process metadata and combines options for collection
             *
             * @returns {Object}
             */
            combineCollectionOptions: function () {
                return _.extend({
                    inputName: this.metadata.options.gridName,
                    parse: true,
                    url: '\/user\/json',
                    state: _.extend({
                        filters: {},
                        sorters:{}
                    }, this.metadata.state)
                }, this.metadata.options);
            },

            /**
             * Process metadata and combines options for datagrid
             *
             * @returns {Object}
             */
            combineGridOptions: function () {
                var columns,
                    rowActions = {},
                    massActions = {},
                    modules = this.modules,
                    metadata = this.metadata;

                // columns
                columns = _.map(metadata.columns, function (cell) {
                    var cellOptionKeys = ['name', 'label', 'renderable', 'editable'],
                        cellOptions = _.pick.apply(null, [cell].concat(cellOptionKeys)),
                        extendOptions = _.omit.apply(null, [cell].concat(cellOptionKeys.concat('type'))),
                        cellType = modules[helpers.cellType(cell.type)];
                        // @todo not sortable columns
                    if (!_.isEmpty(extendOptions)) {
                        cellType = cellType.extend(extendOptions);
                    }
                    cellOptions.cell = cellType;
                    return cellOptions;
                });

                // row actions
                _.each(metadata.rowActions, function (options, action) {
                    rowActions[action] = modules[helpers.actionType(options.type)].extend(options);
                });

                // mass actions
                _.each(metadata.massActions, function (options, action) {
                    massActions[action] = modules[helpers.actionType(options.type)].extend(options);
                });

                return {
                    name: metadata.options.gridName,
                    columns: columns,
                    rowActions: rowActions,
                    massActions: massActions,
                    toolbarOptions: metadata.options.toolbarOptions || {},
                    multipleSorting: metadata.options.multipleSorting || false,
                    entityHint: metadata.options.entityHint
                };
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

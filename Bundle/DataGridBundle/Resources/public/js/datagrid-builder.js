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
        types = {
            cell: {
                date:     'moment',
                datetime: 'moment',
                decimal:  'number'
            },
            action: ['navigate', 'delete', 'ajax', 'mass']
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
                    moduleName = function (template, type) {
                        return template.replace('{{type}}', type);
                    };
                // cells
                _.each(this.metadata.columns, function (column) {
                    var type = column.type;
                    modules[helpers.cellType(type)] = moduleName(cellModuleName, types.cell[type] || type);
                });
                // actions
                _.each(types.action, function (type) {
                    modules[helpers.actionType(type)] = moduleName(actionModuleName, type);
                });
            },

            /**
             * Build grid
             */
            buildGrid: function () {
                var options, collection, grid,
                    gridName = this.metadata.options.gridName;

                // create collection
                options = methods.combineCollectionOptions.call(this);
                collection = new PageableCollection(this.$el.data('data'), options);
                mediator.trigger('datagrid_collection_set_after', collection, this.$el);

                // create grid
                options = methods.combineGridOptions.call(this);
                grid = new Grid(_.extend({collection: collection}, options));
                this.$el.append(grid.render().$el);
                registry.setElement('datagrid', gridName, grid);
                mediator.trigger('datagrid:created:' + gridName, grid);

                // create grid view
                $(gridGridViewsSelector).append((new GridViewsView({collection: collection})).render().$el);

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
                    modules = this.modules,
                    metadata = this.metadata;

                // columns
                columns = _.map(this.metadata.columns, function (cell) {
                    var optionKeys = ['name', 'label', 'renderable', 'editable'],
                        options = _.pick.apply(null, [cell].concat(optionKeys)),
                        cellOptions = _.omit.apply(null, [cell].concat(optionKeys.concat('type'))),
                        cellType = modules[helpers.cellType(cell.type)];
                    if (!_.isEmpty(cellOptions)) {
                        cellType = cellType.extend(cellOptions);
                    }
                    options.cell = cellType;
                    return options;
                });

                return {
                    name: this.metadata.options.gridName,
                    columns: columns,
                    rowActions: this.metadata.rowActions,
                    massActions: this.metadata.massActions,
                    toolbarOptions: this.metadata.options.toolbarOptions || {},
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

/**
 * @deprecated
 * @TODO - Will be removed in TIP-733-2
 */
'use strict';

define([
        'jquery',
        'underscore',
        'backbone',
        'oro/datagrid-builder',
        'routing',
        'oro/mediator',
        'pim/template/form/grid',
        'oro/pageable-collection',
        'pim/datagrid/state',
        'oro/error',
        'require-context'
    ],
    function (
        $,
        _,
        Backbone,
        datagridBuilder,
        Routing,
        mediator,
        template,
        PageableCollection,
        DatagridState,
        Error,
        requireContext
    ) {
        return Backbone.View.extend({
            template: _.template(template),
            urlParams: {},

            /**
             * {@inheritdoc}
             */
            initialize: function (alias, options) {
                this.alias = alias;
                this.selection = options.selection || [];
                this.selection = _.map(this.selection, function (item) {
                    return String(item);
                });
                this.options = options;

                var selectionIdentifier = options.selectionIdentifier || 'id';

                /*
                 * Removing to be sure that this property will not be used in URLs generated to load the data
                 * The selection is never used back side to load the data and it can generate an URL too long.
                 * The rightful usages of the selection are done with the property "this.selection"
                 */
                delete this.options.selection;

                mediator.on('datagrid:selectModel:' + this.alias, function (model) {
                    this.addElement(model.get(selectionIdentifier));
                }.bind(this));

                mediator.on('datagrid:unselectModel:' + this.alias, function (model) {
                    this.removeElement(model.get(selectionIdentifier));
                }.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({}));

                this.renderGrid(this.alias, this.options);

                return this;
            },

            /**
             * Render the given grid
             *
             * @param {String} alias
             * @param {Object} params
             */
            renderGrid: function (alias, params) {
                this.urlParams = $.extend(true, {}, params);
                this.urlParams.alias = alias;
                this.urlParams.params = $.extend(true, {}, params);
                this.urlParams[alias] = $.extend(true, {}, params);

                var viewStored = DatagridState.get(alias, ['view']);
                if (!viewStored.view) {
                    DatagridState.refreshFiltersFromUrl(alias);
                }

                var state = DatagridState.get(alias, ['view', 'filters', 'columns']) || {};
                this.applyView(state.view, alias);
                this.applyFilters(state.filters, alias);
                this.applyColumns(state.columns, alias);

                //TODO Manage columns for product form (when refactoring product form index)
                //TODO Manage category filter (when refactoring category index)
                $.get(Routing.generate('pim_datagrid_load', this.urlParams)).then(function (response) {
                    this.$el.find('.grid-drop').data({
                        metadata: response.metadata,
                        data: JSON.parse(response.data)
                    });

                    var modules = response.metadata.requireJSModules.concat('pim/datagrid/state-listener');

                    var resolvedModules = []
                    _.each(modules, function(module) {
                        resolvedModules.push(requireContext(module))
                    })
                    datagridBuilder(resolvedModules)
                }.bind(this))
                .fail(function(response) {
                    Error.dispatch(null, response);
                });
            },

            /**
             * Get the current grid selection
             *
             * @return {Array}
             */
            getSelection: function () {
                return this.selection;
            },

            /**
             * Add an element to the selection
             *
             * @param {Object} element
             */
            addElement: function (element) {
                this.selection = _.union(this.selection, [element]);
                this.trigger('grid:selection:updated', this.selection);
            },

            /**
             * Remove an element to the selection
             *
             * @param {Object} element
             */
            removeElement: function (element) {
                this.selection = _.without(this.selection, element);
                this.trigger('grid:selection:updated', this.selection);
            },

            /**
             * Ask for a refresh of the grid (aware that we should not call the mediator for that but we don't have
             * the choice for now)
             */
            refresh: function () {
                mediator.trigger('datagrid:doRefresh:' + this.alias);
            },

            /**
             * Apply the view to the DatagridState
             * @param viewId
             * @param alias
             */
            applyView: function (viewId, alias) {
                if (!viewId) {
                    return;
                }

                this.urlParams[alias + '[_parameters][view][id]'] = viewId;

                DatagridState.set(alias, {
                    view: viewId
                });
            },

            /**
             * Apply the filters to the DatagridState
             * @param rawFilters
             * @param alias
             */
            applyFilters: function (rawFilters, alias) {
                if (!rawFilters) {
                    return;
                }

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
                collection.processFiltersParams(this.urlParams, filters, alias + '[_filter]');

                for (var column in filters.sorters) {
                    this.urlParams[alias + '[_sort_by][' + column + ']'] =
                        1 === parseInt(filters.sorters[column]) ?
                            'DESC' :
                            'ASC';
                }

                if (undefined !== filters.pageSize) {
                    this.urlParams[alias + '[_pager][_per_page]'] = 25;
                }

                if (undefined !== filters.currentPage) {
                    this.urlParams[alias + '[_pager][_page]'] = filters.currentPage;
                }

                DatagridState.set(alias, {
                    filters: rawFilters
                });
            },

            /**
             * Apply the columns to the DatagridState
             * @param columns
             * @param alias
             */
            applyColumns: function (columns, alias) {
                if (!columns) {
                    return;
                }

                if (_.isArray(columns)) {
                    columns = columns.join();
                }
                this.urlParams[alias + '[_parameters][view][columns]'] = columns;

                DatagridState.set(alias, {
                    columns: columns
                });
            }
        });
    }
);

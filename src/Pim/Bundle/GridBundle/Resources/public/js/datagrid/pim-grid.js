define(
    ['underscore', 'oro/grid/grid', 'pim/datagrid/export-action', 'pim/datagrid/toolbar'],
    function (_, OroGrid, ExportCollectionAction, Toolbar) {
        'use strict';

        /**
         * Pim grid class extending Oro Datagrid adding export actions
         *
         * @author  Romain Monceau <romain@akeneo.com>
         * @class   Pim.Datagrid.Grid
         * @extends Oro.Datagrid.Grid
         * @see     Backgrid.Grid
         */
        var Grid = OroGrid.extend({
            /**
             * @property {Object} Default properties values
             */
            defaults: {
                noDataHint: 'No data found.',
                noResultsHint: 'No items found during search.',
                rowClickActionClass: 'row-click-action',
                rowClassName: '',
                toolbarOptions: {addResetAction: true, addRefreshAction: true},
                rowClickAction: undefined,
                multipleSorting: true,
                rowActions: [],
                massActions: [],
                exportActions: []
            },
            /** @property {pim.datagrid.Toolbar} */
            toolbar: Toolbar,

            /**
             * @override
             * Add export actions in toolbar
             *
             * @param {Object} toolbarOptions
             * @return {pim.datagrid.Toolbar}
             * @private
             */
            _createToolbar:function(toolbarOptions) {
                return new this.toolbar(_.extend({}, toolbarOptions, {
                    collection: this.collection,
                    actions: this._getToolbarActions(),
                    massActions: this._getToolbarMassActions(),
                    exportActions: this._getToolbarExportActions()
                }));
            },

            /**
             * Get toolbar export actions
             *
             * @return {Array}
             * @private
             */
            _getToolbarExportActions: function() {
                var result = [];

                _.each(this.exportActions, function(action) {
                    result.push(this.createExportAction(action.prototype));
                }, this);

                return result;
            },

            /**
             * Creates export action
             *
             * @param {Function} actionPrototype
             * @return Pim.Datagrid.Action.ExportCollectionAction
             * @protected
             */
            createExportAction: function(actionPrototype) {
                return new ExportCollectionAction({
                    datagrid: this,
                    baseUrl: actionPrototype.baseUrl,
                    keepParameters: actionPrototype.keepParameters,
                    launcherOptions: {
                        label: actionPrototype.label,
                        className: 'btn no-hash',
                        iconClassName: actionPrototype.icon
                    }
                });
            }
        });
        return Grid;
    }
);

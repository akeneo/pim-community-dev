define(
    ['underscore', 'oro/datagrid/grid', 'pim/datagrid/export-action'],
    function (_, Grid, ExportCollectionAction) {
        'use strict';

        /**
         * Pim grid class extending Oro Datagrid adding export actions
         * 
         * @author  Romain Monceau <romain@akeneo.com>
         * @class   Pim.Datagrid.Grid
         * @extends Oro.Datagrid.Grid
         * @see     Backgrid.Grid
         */
        return Grid.extend({
            /**
             * @property {Object} Default properties values
             */
            defaults: {
                noDataHint: 'No data found.',
                noResultsHint: 'No items found during search.',
                rowClickActionClass: 'row-click-action',
                rowClassName: '',
                toolbarOptions: {},
                addResetAction: true,
                addRefreshAction: true,
                addExportAction: true,
                rowClickAction: undefined,
                rowActions: [],
                massActions: [],
                exportActions: []
            },

            /**
             * Override get mass actions of toolbar adding export actions
             *
             * @return {Array}
             * @private
             */
            _getToolbarMassActions: function() {
                var result = [];
                _.each(this.massActions, function(action) {
                    result.push(this.createMassAction(action));
                }, this);
                
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
    }
);


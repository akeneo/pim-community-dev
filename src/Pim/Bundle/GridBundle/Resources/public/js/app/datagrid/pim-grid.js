var Pim = Pim || {};
Pim.Datagrid = Pim.Datagrid || {};

/**
 * Pim grid class extending Oro Datagrid adding export action features
 * 
 * @author  Romain Monceau <romain@akeneo.com>
 * @class   Pim.Datagrid.Grid
 * @extends Oro.Datagrid.Grid
 * @see     Backgrid.Grid
 */
Pim.Datagrid.Grid = Oro.Datagrid.Grid.extend({
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
     * Override get mass actions of toolbar adding export actions in it
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
            result.push(this.createExportAction(action));
        }, this);

        return result;
    },
    
    /**
     * Creates export action
     * 
     * @param {Function} action
     * @return Pim.Datagrid.Action.ExportCollectionAction
     * @protected
     */
    createExportAction: function(action) {
        return new Pim.Datagrid.Action.ExportCollectionAction({
            datagrid: this,
            baseUrl: action.prototype.baseUrl,
            launcherOptions: {
                label: action.prototype.label,
                className: 'btn',
                iconClassName: action.prototype.icon
            }
        });
    }
});

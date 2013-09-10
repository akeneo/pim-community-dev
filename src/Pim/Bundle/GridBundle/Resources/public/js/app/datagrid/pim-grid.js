var Pim = Pim || {};
Pim.Datagrid = Pim.Datagrid || {};

/**
 * Pim grid class extending Oro Datagrid for quick export action
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
        addQuickExportAction: true,
        rowClickAction: undefined,
        rowActions: [],
        massActions: [],
        exportActions: []
    },

    /**
     * Get mass actions of toolbar
     *
     * @return {Array}
     * @private
     */
    _getToolbarMassActions: function() {
        var result = [];
        _.each(this.massActions, function(action) {
            result.push(this.createMassAction(action));
        }, this);
        if (this.addQuickExportAction) {
            result.push(this.getQuickExportAction());
        }

        return result;
    },

    /**
     * Get action that quick export grid's collection
     *
     * @return Pim.Datagrid.Action.QuickExportCollectionAction
     */
    getQuickExportAction: function() {
        if (!this.quickExportAction) {
            this.quickExportAction = new Pim.Datagrid.Action.QuickExportCollectionAction({
                datagrid: this,
                baseUrl: Routing.generate('pim_catalog_product_index', {'_format': 'csv'}),
                launcherOptions: {
                    label: 'Quick export',
                    className: 'btn no-hash',
                    iconClassName: 'icon-download'
                }
            });
        }
        
        return this.quickExportAction;
    }
});

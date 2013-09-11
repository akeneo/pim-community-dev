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
        addExportAction: true,
        rowClickAction: undefined,
        rowActions: [],
        massActions: [],
        exportActions: []
    },
    
    initialize: function(options) {
        options = options || {};
        
        Oro.Datagrid.Grid.prototype.initialize.apply(this, arguments);
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
        
        _.each(this.exportActions, function(action) {
            result.push(this.createExportAction(action.prototype));
        }, this);

        return result;
    },
    
    createExportAction: function(actionPrototype) {
        return new Pim.Datagrid.Action.ExportCollectionAction({
            datagrid: this,
            baseUrl: actionPrototype.baseUrl,
            launcherOptions: {
                label: actionPrototype.label,
                className: 'btn',
                iconClassName: actionPrototype.icon
            }
        });
    },

    /**
     * Get action that export grid's collection
     *
     * @return Pim.Datagrid.Action.ExportCollectionAction
     */
    getExportAction: function() {
        if (!this.exportAction) {
            this.exportAction = new Pim.Datagrid.Action.ExportCollectionAction({
                datagrid: this,
                baseUrl: Routing.generate('pim_catalog_product_index', {'_format': 'csv'}),
                launcherOptions: {
                    label: 'Quick export',
                    className: 'btn no-hash',
                    iconClassName: 'icon-download'
                }
            });
        }
        
        return this.exportAction;
    }
});

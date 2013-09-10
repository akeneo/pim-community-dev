var Pim = Pim || {};
Pim.Datagrid = Pim.Datagrid || {};

Pim.Datagrid.Grid = Oro.Datagrid.Grid.extend({

    /** @property {Oro.Datagrid.Toolbar} */
    toolbar: Oro.Datagrid.Toolbar,

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
        massActions: []
    },

    /**
     * Creates instance of toolbar
     *
     * @param {Object} toolbarOptions
     * @return {Oro.Datagrid.Toolbar}
     * @private
     */
    _createToolbar: function(toolbarOptions) {
        return new this.toolbar(_.extend({}, toolbarOptions, {
            collection: this.collection,
            actions: this._getToolbarActions(),
            massActions: this._getToolbarMassActions(),
            //TODO : Add export actiosn
        }));
    },

    /**
     * Get actions of toolbar
     *
     * @return {Array}
     * @private
     * 
     * TODO : Must be removed
     */
    _getToolbarActions: function() {
        var result = [];
        //TODO : Call Oro.Datagrid.Grid
        if (this.addRefreshAction) {
            result.push(this.getRefreshAction());
        }
        if (this.addResetAction) {
            result.push(this.getResetAction());
        }
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

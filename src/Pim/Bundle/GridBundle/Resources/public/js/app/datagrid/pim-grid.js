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
        rowClickAction: undefined,
        rowActions: [],
        massActions: []
    },

    /**
     * Initialize grid
     *
     * @param {Object} options
     * @param {Backbone.Collection} options.collection
     * @param {Backbone.Collection|Array} options.columns
     * @param {String} [options.noDataHint] Text which displayed when datagrid collection is empty
     * @param {String} [options.rowClickActionClass] CSS class for row with click action
     * @param {String} [options.rowClassName] CSS class for row
     * @param {Object} [options.toolbarOptions] Options for toolbar
     * @param {Boolean} [options.addResetAction] If TRUE reset action will be added in toolbar
     * @param {Boolean} [options.addRefreshAction] If TRUE refresh action will be added in toolbar
     * @param {Oro.Datagrid.Action.AbstractAction[]} [options.rowActions] Array of row actions prototypes
     * @param {Oro.Datagrid.Action.AbstractAction[]} [options.massActions] Array of mass actions prototypes
     * @param {Oro.Datagrid.Action.AbstractAction} [options.rowClickAction] Prototype for action that handles row click
     * @throws {TypeError} If mandatory options are undefined
     */
    initialize: function(options) {
        options = options || {};

        // Check required options
        if (!options.collection) {
            throw new TypeError("'collection' is required")
        }
        this.collection = options.collection;

        if (!options.columns) {
            throw new TypeError("'columns' is required")
        }

        // Init properties values based on options and defaults
        _.extend(this, this.defaults, options);

        this._initRowActions();

        if (this.rowClickAction) {
            // This option property is used in Oro.Datagrid.Body
            options.rowClassName = this.rowClickActionClass + ' ' + this.rowClassName;
        }

        options.columns.push(this._createActionsColumn());
        options.columns.unshift(this._getMassActionsColumn());

        this.loadingMask = this._createLoadingMask();
        this.toolbar = this._createToolbar(_.extend(this.toolbarOptions, options.toolbarOptions));

        Backgrid.Grid.prototype.initialize.apply(this, arguments);

        // Listen and proxy events
        this._listenToCollectionEvents();
        this._listenToBodyEvents();
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
            massActions: this._getToolbarMassActions()
        }));
    },

    /**
     * Get actions of toolbar
     *
     * @return {Array}
     * @private
     */
    _getToolbarActions: function() {
        var result = [];
        if (this.addRefreshAction) {
            result.push(this.getRefreshAction());
        }
        if (this.addResetAction) {
            result.push(this.getResetAction());
        }
        if (this.addRefreshAction) {
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

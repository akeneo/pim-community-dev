var Pim = Pim || {};
Pim.Datagrid = Pim.Datagrid || {};
Pim.Datagrid.Action = Pim.Datagrid.Action || {};

Pim.Datagrid.Action.QuickExportCollectionAction = Oro.Datagrid.Action.AbstractAction.extend({

    /** @property Backbone.Collection */
    collection: undefined,

    /**
     * Initialize action
     *
     * @param {Object} options
     * @param {Backbone.Collection} options.collection Collection
     * @throws {TypeError} If collection is undefined
     */
    initialize: function(options) {
        options = options || {};

        if (!options.datagrid) {
            throw new TypeError("'datagrid' is required");
        }
        this.collection = options.datagrid.collection;

        Oro.Datagrid.Action.AbstractAction.prototype.initialize.apply(this, arguments);
    },

    /**
     * Execute refresh collection
     */
    execute: function() {
        this.collection.fetch();
    }
});
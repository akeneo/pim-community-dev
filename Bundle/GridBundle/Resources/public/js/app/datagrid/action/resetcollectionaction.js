var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Action = Oro.Datagrid.Action || {};

/**
 * Resets collection to initial state
 *
 * @class   Oro.Datagrid.Action.ResetCollectionAction
 * @extends Oro.Datagrid.Action.AbstractAction
 */
Oro.Datagrid.Action.ResetCollectionAction = Oro.Datagrid.Action.AbstractAction.extend({

    /** @property Oro.PageableCollection */
    collection: undefined,

    /**
     * Initialize action
     *
     * @param {Object} options
     * @param {Oro.PageableCollection} options.collection Collection
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
     * Execute reset collection
     */
    execute: function() {
        this.collection.updateState(this.collection.initialState);
        this.collection.fetch();
    }
});

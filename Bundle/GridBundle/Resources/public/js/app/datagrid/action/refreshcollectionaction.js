var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Action = Oro.Datagrid.Action || {};

/**
 * Refreshes collection
 *
 * @class   Oro.Datagrid.Action.RefreshCollectionAction
 * @extends Oro.Datagrid.Action.AbstractAction
 */
Oro.Datagrid.Action.RefreshCollectionAction = Oro.Datagrid.Action.AbstractAction.extend({

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

        if (!options.collection) {
            throw new TypeError("'collection' is required");
        }
        this.collection = options.collection;

        Oro.Datagrid.Action.AbstractAction.prototype.initialize.apply(this, arguments);
    },

    /**
     * Execute refresh collection
     */
    execute: function() {
        this.collection.fetch();
    }
});

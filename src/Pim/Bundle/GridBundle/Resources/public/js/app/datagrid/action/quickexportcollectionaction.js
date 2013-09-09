var Pim = Pim || {};
Pim.Datagrid = Pim.Datagrid || {};
Pim.Datagrid.Action = Pim.Datagrid.Action || {};

/**
 * Quick export collection
 * 
 * @author  Romain Monceau <romain@akeneo.com>
 * @class   Pim.Datagrid.Action.QuickExportCollectionAction
 * @extends Oro.Datagrid.Action.AbstractAction
 */
Pim.Datagrid.Action.QuickExportCollectionAction = Oro.Datagrid.Action.AbstractAction.extend({

    /** @property Backbone.Collection */
    collection: undefined,
    
    /** @property boolean */
    useDirectLauncherLink: true,

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
     * Get link
     */
    getLink: function() {
        return document.location.href;
    }
});
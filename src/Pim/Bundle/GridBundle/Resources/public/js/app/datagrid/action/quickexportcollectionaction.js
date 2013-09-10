var Pim = Pim || {};
Pim.Datagrid = Pim.Datagrid || {};
Pim.Datagrid.Action = Pim.Datagrid.Action || {};

/**
 * Quick export collection action extending NavigateAction
 * 
 * @author  Romain Monceau <romain@akeneo.com>
 * @class   Pim.Datagrid.Action.QuickExportCollectionAction
 * @extends Oro.Datagrid.Action.NavigateAction
 * @see     Oro.Datagrid.Action.AbstractAction
 */
Pim.Datagrid.Action.QuickExportCollectionAction = Oro.Datagrid.Action.NavigateAction.extend({

    /**
     * Initialize collection and launcher
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

        this.launcherOptions = _.extend({
            link: this.getLink(),
            runAction: true
        }, this.launcherOptions);

        Oro.Datagrid.Action.AbstractAction.prototype.initialize.apply(this, arguments);
    },
    
    /**
     * Execution when clicking on the button
     * Open a new window to download the file come from the action called
     */
    execute: function() {
        window.open(this.getLink());
    },
    
    /**
     * Get the link of the file
     * 
     * @return string
     */
    getLink: function() {
        var data = {};
        data = this.collection.processQueryParams(data, this.collection.state);
        data = this.collection.processFiltersParams(data, this.collection.state);
        data = Oro.packToQueryString(data);
        
        var baseUrl = Routing.generate('pim_catalog_product_index', {'_format': 'csv'});
        
        return baseUrl.concat('?'+data);
    }
});
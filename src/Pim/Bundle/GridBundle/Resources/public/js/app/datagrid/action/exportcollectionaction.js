var Pim = Pim || {};
Pim.Datagrid = Pim.Datagrid || {};
Pim.Datagrid.Action = Pim.Datagrid.Action || {};

/**
 * Export collection action
 * 
 * @author  Romain Monceau <romain@akeneo.com>
 * @class   Pim.Datagrid.Action.ExportCollectionAction
 * @extends Oro.Datagrid.Action.AbstractAction
 */
Pim.Datagrid.Action.ExportCollectionAction = Oro.Datagrid.Action.AbstractAction.extend({
    /**
     * The base url of the action called
     * 
     * @property {String}
     */
    baseUrl: null,
    
    /**
     * Define if the action must keep filters, sorters and pagination or not
     * 
     * @property {Boolean}
     */
    keepParameters: true,
    
    /**
     * Initialize collection and launcher
     * 
     * @param {Object} options
     * @param {Backbone.Collection} options.collection Collection
     * @param {String} options.baseUrl
     * @throws {TypeError} If collection is undefined
     * @throws {TypeError} If collection is undefined
     */
    initialize: function(options) {
        options = options || {};

        if (!options.baseUrl) {
            throw new TypeError("'baseUrl' is required");
        }
        this.baseUrl = options.baseUrl;
        this.keepParameters = (!options.keepParameters) ? true : options.keepParameters;
        
        if (!options.datagrid || !options.datagrid.collection) {
            throw new TypeError("'datagrid' and 'collection' are required");
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
     * Get the link of the returned action
     * 
     * @return {String}
     */
    getLink: function() {
        if (!this.keepParameters) {
            return this.baseUrl;
        }
        
        var data = {};
        data = this.collection.processQueryParams(data, this.collection.state);
        data = this.collection.processFiltersParams(data, this.collection.state);
        data = Oro.packToQueryString(data);
        
        if (data === null) {
            return this.baseUrl;
        }
        
        return this.baseUrl.concat('?'+data);
    },
});

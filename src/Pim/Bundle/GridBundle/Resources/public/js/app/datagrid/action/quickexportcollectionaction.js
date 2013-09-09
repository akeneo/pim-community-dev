var Pim = Pim || {};
Pim.Datagrid = Pim.Datagrid || {};
Pim.Datagrid.Action = Pim.Datagrid.Action || {};

Pim.Datagrid.Action.QuickExportCollectionAction = Oro.Datagrid.Action.NavigateAction.extend({

    initialize: function(options) {
        options = options || {};

        if (!options.datagrid) {
            throw new TypeError("'datagrid' is required");
        }
        this.collection = options.datagrid.collection;

        this.on('preExecute', _.bind(this._preExecuteSubscriber, this));
        this.launcherOptions = _.extend({
            link: this.getLink(),
            runAction: false
        }, this.launcherOptions);

        Oro.Datagrid.Action.AbstractAction.prototype.initialize.apply(this, arguments);
    },

    /**
     * Execute refresh collection
     */
    execute: function() {
        this.collection.fetch();
    },
    
    getLink: function() {
        var data = {};
        data = this.collection.processQueryParams(data, this.collection.state);
        data = this.collection.processFiltersParams(data, this.collection.state);
        data = Oro.packToQueryString(data);
        
        var baseUrl = Routing.generate('pim_catalog_product_index', {'_format': 'csv'});
        var url = baseUrl.concat('?'+data);
        
        return url;
    }
});
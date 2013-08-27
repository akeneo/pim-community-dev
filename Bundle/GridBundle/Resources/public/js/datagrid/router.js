var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};

/**
 * Router for basic datagrid
 *
 * @class   Oro.Datagrid.Router
 * @extends Backbone.Router
 */
Oro.Datagrid.Router = Backbone.Router.extend({
    /** @property */
    routes: {
        "g/*encodedStateData": "changeState"
    },

    /**
     * Binded collection, passed in constructor as option
     *
     * @property {Oro.PageableCollection}
     */
    collection: null,

    /**
     * Initial state of binded collection, passed in constructor
     *
     * @property {Object}
     */
    _initState: null,

    /**
     * Initialize router
     *
     * @param {Object} options
     * @param {Oro.PageableCollection} options.collection Collection of models.
     */
    initialize: function(options) {
        options = options || {};
        if (!options.collection) {
            throw new TypeError("'collection' is required");
        }

        this.collection = options.collection;
        this._initState = _.clone(this.collection.state);

        this.collection.on('beforeReset', this._handleStateChange, this);

        Backbone.Router.prototype.initialize.apply(this, arguments);
        /**
         * Backbone event. Fired when grid route is initialized
         * @event grid_route:loaded
         */
        Oro.Events.trigger("grid_route:loaded", this);
    },

    /**
     * Triggers when collection is has new state and fetched
     *
     * @param {Oro.PageableCollection} collection
     * @param {Object} options Fetch options
     * @private
     */
    _handleStateChange: function(collection, options) {
        options = options || {};
        if (options.ignoreSaveStateInUrl) {
            return;
        }
        var encodedStateData = collection.encodeStateData(collection.state);
        var url = '';
        if (Oro.hashNavigationEnabled()) {
            url = 'url=' + Oro.hashNavigationInstance.getHashUrl() + '|g/' + encodedStateData;
        } else {
            url = 'g/' + encodedStateData;
        }
        this.navigate(url);
    },

    /**
     * Route for change state of collection.
     *
     * @param {String} encodedStateData String represents encoded state stored in URL
     */
    changeState: function(encodedStateData) {
        var state = null;
        if (encodedStateData) {
            state = this.collection.decodeStateData(encodedStateData);
        } else {
            state = this._initState;
        }
        this.collection.updateState(state);
        this.collection.fetch({
            ignoreSaveStateInUrl: true
        });
    }
});

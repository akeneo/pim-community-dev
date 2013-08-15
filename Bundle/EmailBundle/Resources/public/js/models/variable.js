Oro = Oro || {};
Oro.Email = Oro.Email || {};
Oro.Email.VariablesUpdater = Oro.Email.VariablesUpdater || {};

Oro.Email.VariablesUpdater.Variable = Backbone.Model.extend({
    defaults: {
        user:   [],
        entity: [],
        entityName: null
    },

    route: 'oro_api_get_emailtemplate_available_variables',
    url: null,

    initialize: function() {
        this.updateUrl();
        this.bind('change:entityName', this.updateUrl, this);
    },

    /**
     * onChange entityName attribute
     */
    updateUrl: function() {
        this.url = Routing.generate(this.route, {entityName: this.get('entityName')});
    }
});

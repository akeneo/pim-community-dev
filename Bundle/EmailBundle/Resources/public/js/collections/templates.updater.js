Oro = Oro || {};
Oro.EmailTemplatesUpdater = Oro.EmailTemplatesUpdater || {};

Oro.EmailTemplatesUpdater.Collection = Backbone.Collection.extend({
    route: 'oro_api_get_emailtemplate',
    url: null,
    model: Oro.EmailTemplatesUpdater.EmailTemplate,

    /**
     * Constructor
     */
    initialize: function () {
        this.url = Routing.generate(this.route, {entityName: null});
    },

    /**
     * Regenerate route for selected entity
     *
     * @param id {String}
     */
    setEntityId: function (id) {
        this.url = Routing.generate(this.route, {entityName: id});
    }
});

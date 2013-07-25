Oro = Oro || {};
Oro.EmailTemplatesUpdater = Oro.EmailTemplatesUpdater || {};

Oro.EmailTemplatesUpdater.Collection = Backbone.Collection.extend({
    route: 'oro_api_email_get_templates',
    url: null,
    model: Oro.EmailTemplatesUpdater.EmailTemplate,

    /**
     * Constructor
     */
    initialize: function () {
        this.url = Routing.generate(this.route);
    },

    /**
     * Regenerate route for selected entity
     *
     * @param id {String}
     */
    setEntityId: function (id) {
        this.url = Routing.generate(this.route, {entity: id});
    }
});

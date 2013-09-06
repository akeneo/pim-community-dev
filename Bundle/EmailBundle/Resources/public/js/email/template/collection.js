/* global define */
define(['backbone', 'routing', 'oro/email/template/model'],
function(Backbone, routing, EmailTemplateModel) {
    'use strict';

    /**
     * @export  oro/email/template/collection
     * @class   oro.email.template.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        route: 'oro_api_get_emailtemplate',
        url: null,
        model: EmailTemplateModel,

        /**
         * Constructor
         */
        initialize: function () {
            this.url = routing.generate(this.route, {entityName: null});
        },

        /**
         * Regenerate route for selected entity
         *
         * @param id {String}
         */
        setEntityId: function (id) {
            this.url = routing.generate(this.route, {entityName: id});
        }
    });
});

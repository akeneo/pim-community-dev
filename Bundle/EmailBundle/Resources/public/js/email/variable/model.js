/* global define */
define(['backbone', 'routing'],
function(Backbone, routing) {
    'use strict';

    /**
     * @export  oro/email/variable/model
     * @class   oro.email.variable.Model
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
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
            this.url = routing.generate(this.route, {entityName: this.get('entityName')});
        }
    });
});

/* global define */
define(['backbone', 'routing', 'oro/region/model'],
function(Backbone, routing, RegionModel) {
    'use strict';

    /**
     * @export  oro/region/collection
     * @class   oro.region.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        route: 'oro_api_country_get_regions',
        url: null,
        model: RegionModel,

        /**
         * Constructor
         */
        initialize: function () {
            this.url = routing.generate(this.route);
        },

        /**
         * Regenerate route for selected country
         *
         * @param id {string}
         */
        setCountryId: function (id) {
            this.url = routing.generate(this.route, {country: id});
        }
    });
});

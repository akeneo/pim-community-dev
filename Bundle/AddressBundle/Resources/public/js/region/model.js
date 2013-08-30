/* global define */
define(['backbone'],
function(Backbone) {
    'use strict';

    /**
     * @export  oro/region/model
     * @class   oro.region.Model
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        defaults: {
            country: '',
            code: '',
            name: ''
        }
    });
});

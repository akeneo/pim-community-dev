/* global define */
define(['backbone'],
function(Backbone) {
    'use strict';

    /**
     * @export  oro/tag/model
     * @class   oro.tag.Model
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        defaults: {
            owner     : false,
            notSaved  : false,
            moreOwners: false,
            url       : '',
            name      : ''
        }
    });
});

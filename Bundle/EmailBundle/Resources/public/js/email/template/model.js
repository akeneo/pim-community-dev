/* global define */
define(['backbone'],
function(Backbone) {
    'use strict';

    /**
     * @export  oro/email/template/model
     * @class   oro.email.template.Model
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        defaults: {
            entity: '',
            id: '',
            name: ''
        }
    });
});

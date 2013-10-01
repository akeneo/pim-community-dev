/* global define */
define(['backbone'],
function(Backbone) {
    'use strict';

    /**
     * @export  oro/address/model
     * @class   oro.address.Model
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        defaults: {
            label: '',
            firstName: '',
            lastName: '',
            street: '',
            street2: '',
            city: '',
            country: '',
            postalCode: '',
            state: '',
            primary: false,
            types: [],
            active: false
        },

        getSearchableString: function() {
            return this.get('country') + ', ' +
                this.get('city') + ', ' +
                this.get('street') + ' ' + (this.get('street2') || '');
        }
    });
});

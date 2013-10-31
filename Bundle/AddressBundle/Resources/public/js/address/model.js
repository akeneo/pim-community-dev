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
            namePrefix: '',
            firstName: '',
            middleName: '',
            lastName: '',
            nameSuffix: '',
            organization: '',
            street: '',
            street2: '',
            city: '',
            country: '',
            countryIso2: '',
            countryIso3: '',
            postalCode: '',
            state: '',
            stateText: '',
            regionCode: '',
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

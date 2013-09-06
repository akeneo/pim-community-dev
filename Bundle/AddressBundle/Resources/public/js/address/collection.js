/* global define */
define(['underscore', 'backbone', 'oro/address/model'],
function(_, Backbone, AddressModel) {
    'use strict';

    /**
     * @export  oro/address/collection
     * @class   oro.address.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        model: AddressModel,

        initialize: function() {
            this.on('change:active', this.onActiveChange, this);
        },

        onActiveChange: function(item) {
            // Only 1 item allowed to be active
            if (item.get('active')) {
                var activeItems = this.where({active: true});
                _.each(activeItems, function(activeItem) {
                    if (activeItem.get('id') !== item.get('id')) {
                        activeItem.set('active', false);
                    }
                });
                this.trigger('activeChange', item);
            }
        }
    });
});

/* global define */
define(['backbone', 'oro/multiple-entity/model'],
function(Backbone, EntityModel) {
    'use strict';

    /**
     * @export  oro/multiple-entity/collection
     * @class   oro.MultipleEntity.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        model: EntityModel,

        initialize: function() {
            this.on('change:isDefault', this.onIsDefaultChange, this);
        },

        onIsDefaultChange: function(item) {
            // Only 1 item allowed to be default
            if (item.get('isDefault')) {
                var defaultItems = this.where({isDefault: true});
                _.each(defaultItems, function(defaultItem) {
                    if (defaultItem.get('id') !== item.get('id')) {
                        defaultItem.set('isDefault', false);
                    }
                });
                this.trigger('defaultChange', item);
            }
        }
    });
});

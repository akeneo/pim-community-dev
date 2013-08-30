var OroAddressCollection = Backbone.Collection.extend({
    model: OroAddress,

    initialize: function() {
        this.on('change:active', this.onActiveChange, this);
    },

    onActiveChange: function(item) {
        // Only 1 item allowed to be active
        if (item.get('active')) {
            var activeItems = this.where({active: true});
            _.each(activeItems, function(activeItem) {
                if (activeItem.get('id') != item.get('id')) {
                    activeItem.set('active', false);
                }
            });
            this.trigger('activeChange', item);
        }
    }
});

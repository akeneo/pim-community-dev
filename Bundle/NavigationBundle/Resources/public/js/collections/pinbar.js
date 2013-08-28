var navigation = navigation || {};
navigation.pinbar = navigation.pinbar || {};

navigation.pinbar.ItemsList = navigation.ItemsList.extend({
    model: navigation.pinbar.Item,

    initialize: function() {
        this.on('change:position', this.onPositionChange, this);
        this.on('change:url', this.onUrlChange, this);
        this.on('change:maximized', this.onStateChange, this);
    },

    onPositionChange: function(item) {
        this.trigger('positionChange', item);
    },

    onStateChange: function(item) {
        this.trigger('stateChange', item);
    },

    onUrlChange: function(item) {
        this.trigger('urlChange', item);
    }
});
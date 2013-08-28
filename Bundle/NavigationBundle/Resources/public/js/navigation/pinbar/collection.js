/* global define */
define(['oro/navigation/collection', 'oro/navigation/pinbar/model'],
function(NavigationCollection, PinbarModel) {
    'use strict';

    /**
     * @export  oro/navigation/pinbar/collection
     * @class   oro.navigation.pinbar.Collection
     * @extends oro.navigation.Collection
     */
    return NavigationCollection.extend({
        model: PinbarModel,

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
});
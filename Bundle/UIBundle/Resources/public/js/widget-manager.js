/* global define */
define(function() {
    'use strict';

    /**
     * @export oro/widget-manager
     * @name   oro.widgetManager
     */
    return {
        types: {},
        widgets: {},

        isSupportedType: function(type) {
            return this.types.hasOwnProperty(type);
        },

        registerWidgetContainer: function(type, initializer) {
            this.types[type] = initializer;
        },

        createWidget: function(type, options) {
            var widget = new this.types[type](options);
            this.widgets[widget.getWid()] = widget;
            return widget;
        },

        getWidgetInstance: function(wid) {
            return this.widgets[wid];
        },

        removeWidget: function(wid) {
            delete this.widgets[wid];
        }
    };
});

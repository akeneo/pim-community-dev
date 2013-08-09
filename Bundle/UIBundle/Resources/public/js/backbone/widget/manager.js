var Oro = Oro || {};
Oro.widget = Oro.widget || {};

Oro.widget.Manager = {
    types: {},
    widgets: {},

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

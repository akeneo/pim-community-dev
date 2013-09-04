var Oro = Oro || {};
Oro.widget = Oro.widget || {};

Oro.widget.Manager = {
    types: {},
    widgets: {},
    aliases: {},

    isSupportedType: function(type) {
        return this.types.hasOwnProperty(type);
    },

    registerWidgetContainer: function(type, initializer) {
        this.types[type] = initializer;
    },

    createWidget: function(type, options) {
        var widget = new this.types[type](options);
        this.widgets[widget.getWid()] = widget;
        if (widget.getAlias()) {
            this.aliases[widget.getAlias()] = widget.getWid();
        }
        return widget;
    },

    getWidgetInstance: function(wid) {
        return this.widgets[wid];
    },

    getWidgetInstanceByAlias: function(alias) {
        if (this.aliases.hasOwnProperty(alias)) {
            return this.getWidgetInstance(this.aliases[alias]);
        }
        return null;
    },

    removeWidget: function(wid) {
        delete this.widgets[wid];
    }
};

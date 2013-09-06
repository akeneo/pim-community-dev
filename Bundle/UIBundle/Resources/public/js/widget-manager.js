/* global define */
define(['oro/mediator'],
function(mediator) {
    'use strict';

    /**
     * @export oro/widget-manager
     * @name   oro.widgetManager
     */
    var widgetManager = {
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

    mediator.on('widget_remove', function(wid) {
        widgetManager.removeWidget(wid);
    });
    return widgetManager;
});

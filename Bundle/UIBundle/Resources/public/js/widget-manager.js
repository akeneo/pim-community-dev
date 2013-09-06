/* global define */
define(['oro/mediator'],
function(mediator) {
    'use strict';

    /**
     * @export oro/widget-manager
     * @name   oro.widgetManager
     */
    var widgetManager = {
        widgets: {},
        aliases: {},

        addWidgetInstance: function(widget) {
            this.widgets[widget.getWid()] = widget;
            mediator.trigger('widget_registration:wid:' + widget.getWid(), widget);
            if (widget.getAlias()) {
                this.aliases[widget.getAlias()] = widget.getWid();
                mediator.trigger('widget_registration:' + widget.getAlias(), widget);
            }
        },

        getWidgetInstance: function(wid, callback) {
            if (this.widgets.hasOwnProperty(wid)) {
                callback(this.widgets[wid]);
            } else {
                mediator.once('widget_registration:wid:' + wid, callback);
            }
        },

        getWidgetInstanceByAlias: function(alias, callback) {
            if (this.aliases.hasOwnProperty(alias)) {
                this.getWidgetInstance(this.aliases[alias], callback);
            } else {
                mediator.once('widget_registration:' + alias, callback);
            }
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

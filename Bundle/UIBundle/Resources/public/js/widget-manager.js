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

        /**
         * Reset manager to initial state.
         */
        resetWidgets: function() {
            this.widgets = {};
            this.aliases = {};
        },

        /**
         * Add widget instance to registry.
         *
         * @param {oro.AbstractWidget} widget
         */
        addWidgetInstance: function(widget) {
            this.widgets[widget.getWid()] = widget;
            mediator.trigger('widget_registration:wid:' + widget.getWid(), widget);
            if (widget.getAlias()) {
                this.aliases[widget.getAlias()] = widget.getWid();
                mediator.trigger('widget_registration:' + widget.getAlias(), widget);
            }
        },

        /**
         * Get widget instance by widget identifier and pass it to callback when became available.
         *
         * @param {string} wid unique widget identifier
         * @param {function} callback widget instance handler
         */
        getWidgetInstance: function(wid, callback) {
            if (this.widgets.hasOwnProperty(wid)) {
                callback(this.widgets[wid]);
            } else {
                mediator.once('widget_registration:wid:' + wid, callback);
            }
        },

        /**
         * Get widget instance by alias and pass it to callback when became available.
         *
         * @param {string} alias widget alias
         * @param {function} callback widget instance handler
         */
        getWidgetInstanceByAlias: function(alias, callback) {
            if (this.aliases.hasOwnProperty(alias)) {
                this.getWidgetInstance(this.aliases[alias], callback);
            } else {
                mediator.once('widget_registration:' + alias, callback);
            }
        },

        /**
         * Remove widget instance from registry.
         *
         * @param {string} wid unique widget identifier
         */
        removeWidget: function(wid) {
            delete this.widgets[wid];
        }
    };

    mediator.on('widget_initialize', function(widget) {
        widgetManager.addWidgetInstance(widget);
    });

    mediator.on('widget_remove', function(wid) {
        widgetManager.removeWidget(wid);
    });

    return widgetManager;
});

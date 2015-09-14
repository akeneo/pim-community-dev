define(
    [
        'backbone'
    ],
    function (Backbone) {
        'use strict';

        var WidgetContainer = Backbone.Model.extend({
            getWidget: function (options, ClassFunction) {
                var widget = null;
                if (!this.has(options.alias)) {
                    widget = new ClassFunction(options);
                    this.set(options.alias, widget);
                } else {
                    widget = this.get(options.alias);
                    widget.setElement(options.el);
                }
                return widget;
            }
        });

        return new WidgetContainer();
    }
);

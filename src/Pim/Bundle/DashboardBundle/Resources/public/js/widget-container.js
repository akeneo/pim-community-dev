define(
    [],
    function () {
        'use strict';

        /**
         * Widget container maintain a widget registry to use them on dashboard.
         */
        return {
            widgetsRegistry: {},
            /**
             * Get or create a widget
             *
             * @param {Object}   options
             * @param {Function} ClassFunction
             *
             * @return {Object} AbstractWidget instance
             */
            getWidget: function (options, ClassFunction) {
                var widget = this.widgetsRegistry[options.alias];
                if (!widget) {
                    widget = new ClassFunction(options);
                    this.widgetsRegistry[options.alias] = widget;
                } else {
                    widget.setElement(options.el);
                }

                return widget;
            }
        };
    }
);

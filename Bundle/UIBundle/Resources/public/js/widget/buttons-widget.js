/* global define */
define(['underscore', 'backbone', 'oro/abstract-widget', 'oro/widget-manager'],
function(_, Backbone, AbstractWidget, WidgetManager) {
    'use strict';

    /**
     * @export  oro/block-widget
     * @class   oro.BlockWidget
     * @extends oro.AbstractWidget
     */
    var widget = AbstractWidget.extend({
        options: _.extend(
            _.extend({}, AbstractWidget.prototype.options),
            {
                cssClass: 'pull-left btn-group icons-holder',
                type: 'buttons'
            }
        ),

        initialize: function(options) {
            options = options || {};
            this.initializeWidget(options);

            this.widget = this.$el;
            this.widget.addClass(this.options.cssClass);
        },

        setTitle: function(title) {
            this.widget.attr('title', title);
        },

        getActionsElement: function() {
            return null;
        },

        show: function() {
            if (!this.$el.data('wid')) {
                if (this.$el.parent().length) {
                    this._showStatic();
                } else {
                    this._showRemote();
                }
            }
            AbstractWidget.prototype.show.apply(this);
        },

        _showStatic: function() {
            var anchorDiv = Backbone.$('<div/>');
            var parent = this.widget.parent();
            anchorDiv.insertAfter(parent);
            anchorDiv.replaceWith(this.widget);
            parent.remove();
        },

        _showRemote: function() {
            this.widget.empty();
            this.widget.append(this.$el);
            this.setElement(this.widget);
        }
    });

    WidgetManager.registerWidgetContainer('buttons', widget);

    return widget;
});

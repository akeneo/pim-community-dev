/* global define */
define(['underscore', 'backbone', 'oro/abstract-widget'],
function(_, Backbone, AbstractWidget) {
    'use strict';

    /**
     * @export  oro/buttons-widget
     * @class   oro.ButtonsWidget
     * @extends oro.AbstractWidget
     */
    return AbstractWidget.extend({
        options: _.extend(
            _.extend({}, AbstractWidget.prototype.options),
            {
                cssClass: 'pull-left btn-group icons-holder',
                type: 'buttons',
                loadingMaskEnabled: false
            }
        ),

        initialize: function(options) {
            options = options || {};

            this.widget = this.$el;
            this.widget.addClass(this.options.cssClass);

            this.initializeWidget(options);
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
});

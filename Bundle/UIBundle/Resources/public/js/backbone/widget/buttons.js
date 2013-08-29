var Oro = Oro || {};
Oro.widget = Oro.widget || {};

Oro.widget.Buttons = Oro.widget.Abstract.extend({
    options: _.extend(
        _.extend({}, Oro.widget.Abstract.prototype.options),
        {
            class: 'pull-left btn-group icons-holder',
            type: 'buttons'
        }
    ),

    initialize: function(options) {
        options = options || {};
        this.initializeWidget(options);

        this.widget = this.$el;
        this.widget.addClass(this.options.class);
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
        Oro.widget.Abstract.prototype.show.apply(this);
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

Oro.widget.Manager.registerWidgetContainer('buttons', Oro.widget.Buttons);

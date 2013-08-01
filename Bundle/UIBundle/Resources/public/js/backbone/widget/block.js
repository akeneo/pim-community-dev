var Oro = Oro || {};
Oro.widget = Oro.widget || {};

Oro.widget.Block = Oro.widget.Abstract.extend({
    options: _.extend(
        Oro.widget.Abstract.prototype.options,
        {
            type: 'block',
            titleEl: '.widget-title',
            actionsEl: '.widget-actions',
            contentEl: '.box-content',
            template: _.template('<div class="box-type1">' +
                '<div class="title">' +
                    '<div class="pull-right widget-actions"></div>' +
                    '<span class="widget-title"><%- title %></span>' +
                '</div>' +
                '<div class="box-content row-fluid"></div>' +
            '</div>')
        }
    ),

    initialize: function(options) {
        options = options || {}
        this.initializeWidget(options);

        var anchorDiv = $('<div/>');
        anchorDiv.after(this.$el);
        this.widget = Backbone.$(this.options.template({
            'title': this.options.title
        }));
        this.widgetContent = this.widget.find(this.options.contentEl);
        this.widgetContent.append(this.$el);
        anchorDiv.replaceWith(this.widget);
    },

    setTitle: function(title) {
        this.options.title = title;
        this._getTitleContainer().html(this.options.title);
    },

    renderActions: function() {
        this._getActionsContainer().append(this.getPreparedActions());
    },

    _getActionsContainer: function() {
        if (this.actionsContainer === undefined) {
            this.actionsContainer = this.widget.find(this.options.actionsEl);
        }
        return this.actionsContainer;
    },

    _getTitleContainer: function() {
        if (this.titleContainer === undefined) {
            this.titleContainer = this.widget.find(this.options.titleEl);
        }
        return this.titleContainer;
    },

    show: function() {
        this.renderActions();
    }
});

Oro.widget.Manager.registerWidgetContainer('block', Oro.widget.Block);

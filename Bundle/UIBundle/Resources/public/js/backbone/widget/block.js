var Oro = Oro || {};
Oro.widget = Oro.widget || {};

Oro.widget.Block = Oro.widget.Abstract.extend({
    options: _.extend(
        _.extend({}, Oro.widget.Abstract.prototype.options),
        {
            type: 'block',
            titleContainer: '.widget-title',
            actionsContainer: '.widget-actions-container',
            contentContainer: '.row-fluid',
            contentClasses: ['box-content'],
            template: _.template('<div class="box-type1">' +
                '<div class="title">' +
                    '<div class="pull-right widget-actions-container"></div>' +
                    '<span class="widget-title"><%- title %></span>' +
                '</div>' +
                '<div class="row-fluid <%= contentClasses.join(\' \') %>"></div>' +
            '</div>')
        }
    ),

    initialize: function(options) {
        options = options || {}
        this.initializeWidget(options);

        this.widget = Backbone.$(this.options.template({
            'title': this.options.title,
            'contentClasses': this.options.contentClasses
        }));
        this.widgetContentContainer = this.widget.find(this.options.contentContainer);
    },

    setTitle: function(title) {
        this.options.title = title;
        this._getTitleContainer().html(this.options.title);
    },

    getActionsElement: function() {
        if (this.actionsContainer === undefined) {
            this.actionsContainer = this.widget.find(this.options.actionsContainer);
        }
        return this.actionsContainer;
    },

    _getTitleContainer: function() {
        if (this.titleContainer === undefined) {
            this.titleContainer = this.widget.find(this.options.titleContainer);
        }
        return this.titleContainer;
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
        anchorDiv.insertAfter(this.$el);
        this.widgetContentContainer.append(this.$el);
        anchorDiv.replaceWith(Backbone.$(this.widget));
    },

    _showRemote: function() {
        this.widgetContentContainer.empty();
        this.widgetContentContainer.append(this.$el);
    }
});

Oro.widget.Manager.registerWidgetContainer('block', Oro.widget.Block);

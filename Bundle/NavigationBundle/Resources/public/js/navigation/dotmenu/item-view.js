var navigation = navigation || {};
navigation.dotMenu = navigation.dotMenu || {};

navigation.dotMenu.ItemView = Backbone.View.extend({
    tagName:  'li',

    template: _.template($("#template-dot-menu-item").html()),

    events: {
        'click .close': 'close',
        'click span': 'activate'
    },

    initialize: function() {
        this.listenTo(this.model, 'destroy', this.remove)
    },

    activate: function(e) {
        var el = Backbone.$(e.currentTarget);
        window.location.href = el.data('url');
    },

    close: function()
    {
        this.model.destroy({wait: true});
    },

    render: function () {
        this.$el.html(
            this.template(this.model.toJSON())
        );
        if (this.model.get('url') ==  window.location.pathname) {
            this.$el.addClass('active');
        }
        return this;
    }
});

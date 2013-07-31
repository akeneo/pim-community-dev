var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};

/**
 * Datagrid page size widget
 *
 * @class   Oro.Datagrid.PageSize
 * @extends Backbone.View
 */
Oro.Datagrid.PageSize = Backbone.View.extend({
    /** @property */
    template: _.template(
        '<label class="control-label"><%- _.__("View per page") %>: &nbsp;</label>' +
        '<div class="btn-group ">' +
            '<button data-toggle="dropdown" class="btn dropdown-toggle <% if (disabled) { %>disabled<% } %>">' +
                '<%= collectionState.pageSize %><span class="caret"></span>' +
            '</button>' +
            '<ul class="dropdown-menu pull-right">' +
                '<% _.each(items, function (item) { %>' +
                    '<li><a href="#"><%= item %></a></li>' +
                '<% }); %>' +
            '</ul>' +
        '</div>'
    ),

    /** @property */
    events: {
        "click a": "onChangePageSize"
    },

    /** @property */
    items: [10, 25, 50, 100],

    /** @property */
    enabled: true,

    /**
     * Initializer.
     *
     * @param {Object} options
     * @param {Backbone.Collection} options.collection
     * @param {Array} [options.items]
     */
    initialize: function (options) {
        options = options || {};

        if (!options.collection) {
            throw new TypeError("'collection' is required");
        }

        if (options.items) {
            this.items = options.items;
        }

        this.collection = options.collection;
        this.listenTo(this.collection, "add", this.render);
        this.listenTo(this.collection, "remove", this.render);
        this.listenTo(this.collection, "reset", this.render);
        Backbone.View.prototype.initialize.call(this, options);
    },

    /**
     * Disable page size
     *
     * @return {*}
     */
    disable: function() {
        this.enabled = false;
        this.render();
        return this;
    },

    /**
     * Enable page size
     *
     * @return {*}
     */
    enable: function() {
        this.enabled = true;
        this.render();
        return this;
    },

    /**
     * jQuery event handler for the page handlers. Goes to the right page upon clicking.
     *
     * @param {Event} e
     */
    onChangePageSize: function (e) {
        e.preventDefault();
        var pageSize = parseInt($(e.target).text());
        if (pageSize !== this.collection.state.pageSize) {
            this.collection.state.pageSize = pageSize;
            this.collection.fetch();
        }
    },

    render: function() {
        this.$el.empty();

        this.$el.append($(this.template({
            disabled: !this.enabled || !this.collection.state.totalRecords,
            collectionState: this.collection.state,
            items: this.items
        })));

        return this;
    }
});

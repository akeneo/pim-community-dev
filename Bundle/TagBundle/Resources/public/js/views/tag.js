Oro = Oro || {};
Oro.Tags = Oro.Tags || {};

Oro.Tags.TagView =  Backbone.View.extend({
    options: {
        filter: null
    },

    /**
     * Constructor
     */
    initialize: function() {
        this.collection = new Oro.Tags.TagCollection();
        this.listenTo(this.getCollection(), 'reset', this.render);
        this.listenTo(this, 'filter', this.render);

        this.template = $('#tag-view-template').html();
    },

    /**
     * Filter collection proxy
     *
     * @returns {*}
     */
    filter: function(e) {
        var $el = $(e.target);
        this.options.filter = $el.data('type');
        this.trigger('filter');

        return this;
    },

    /**
     * Get collection object
     *
     * @returns {*}
     */
    getCollection: function() {
        return this.collection;
    },

    /**
     * Render widget
     *
     * @returns {}
     */
    render: function() {
        $('#tags').html(
            _.template(
                this.template,
                {
                    "collection": this.getCollection().getFilteredCollection(this.options.filter)
                }
            )
        );
        // process filter action binding
        $('#tag-sort-actions a').click(_.bind(this.filter, this));

        // process tag click redirect
        if (Oro.hashNavigationEnabled()) {
            var navigationObject = Oro.Registry.getElement("oro.hashnavigation.object");
            navigationObject.processClicks($('#tag-list a'));
        }

        return this;
    }
});

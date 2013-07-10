Oro = Oro || {};
Oro.Tags = Oro.Tags || {};

Oro.Tags.Select2View =  Oro.Tags.TagView.extend({
    options: {
        tagInputId: null
    },

    /**
     * Constructor
     */
    initialize: function() {
        this.collection = new Oro.Tags.TagCollection();
        this.listenTo(this.getCollection(), 'reset', this.render);
        this.listenTo(this, 'filter', this.render);

        $('#tag-sort-actions a').click(_.bind(this.filter, this));
    },

    /**
     * Render widget
     *
     * @returns {}
     */
    render: function() {
        var tagCollection = this.getCollection().getFilteredCollection(this.options.filter);
        var tagArray = [];
        _.each(tagCollection.models, function(tag, i) {
            tagArray.push({
                id: tag.get('id'),
                name: tag.get('name'),
                url: tag.get('url')
            });
        });

        $(this.options.tagInputId).select2("data", tagArray);

        $('.select2-search-choice div').click(function(){
            var tagName = $(this).attr('title');
            var tag = tagArray.filter(function(item){ return item.name == tagName })
            var url = tag[0].url;

            if (Oro.hashNavigationEnabled()) {
                var navigationObject = Oro.Registry.getElement("oro.hashnavigation.object");
                navigationObject.setLocation(url);
            } else {
                window.location = url;
            }
        });

        return this;
    }
});

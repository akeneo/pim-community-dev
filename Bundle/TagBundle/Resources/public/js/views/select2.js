Oro = Oro || {};
Oro.Tags = Oro.Tags || {};

Oro.Tags.Select2View =  Oro.Tags.TagView.extend({
    options: {
        tagInputId: null,
        tags: null,
    },

    /**
     * Constructor
     */
    initialize: function() {
        this.collection = new Oro.Tags.TagCollection();
        this.listenTo(this.getCollection(), 'reset', this.render);
        this.listenTo(this, 'filter', this.render);

        $('#tag-sort-actions a').click(_.bind(this.filter, this));

        self = this;
        $(this.options.tagInputId).on("change", this.updateHiddenInputs);

        if (this.options.tags != null) {
            this.getCollection().reset(this.options.tags);
        }
    },

    updateHiddenInputs: function(event) {
        var owner = self.options.filter == undefined ? 'all' : self.options.filter;

        if (event && event.added) {
            event.added.owner = true;
            owner = 'owner';
            self.getCollection().add(event.added);
        }
        else if (event && event.removed) {
            self.getCollection().remove(event.removed);
        }

        var tagCollection = self.getCollection().getFilteredCollection(self.options.filter);

        var val = tagCollection.toArray();
        var ids = tagCollection.pluck('id');

        $(self.options.tagInputId + '_' + owner).val(ids);

        $(self.options.tagInputId).select2('data', val);
    },

    /**
     * Render widget
     *
     * @returns {}
     */
    render: function() {
        var tagCollection = this.getCollection().getFilteredCollection(this.options.filter);

        this.updateHiddenInputs();

        $('.select2-search-choice div').click(function(){
            var tagName = $(this).attr('title');
            var tag = tagCollection.toArray().filter(function(item){ return item.name == tagName })
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

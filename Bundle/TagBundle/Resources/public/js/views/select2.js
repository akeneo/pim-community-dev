Oro = Oro || {};
Oro.Tags = Oro.Tags || {};

Oro.Tags.Select2View =  Oro.Tags.TagView.extend({
    options: {
        tagInputId: null,
        tags: null
    },

    /**
     * Constructor
     */
    initialize: function() {
        this.collection = new Oro.Tags.TagCollection();
        this.ownCollection = new Oro.Tags.TagCollection();

        this.listenTo(this.getCollection(), 'reset', this.render);
        this.listenTo(this.getCollection('owner'), 'reset', this.render);
        this.listenTo(this, 'filter', this.render);

        $('#tag-sort-actions a').click(_.bind(this.filter, this));

        self = this;
        $(this.options.tagInputId).on("change", this.updateHiddenInputs);

        if (this.options.tags != null) {
            this.getCollection().reset(this.options.tags);
            this.getCollection('owner').reset(this.getCollection().getFilteredCollection('owner').models);
        }
    },

    getCollection: function(type) {
        if (type == undefined || type == 'all') {
            return this.collection;
        } else if (type == 'owner') {
            return this.ownCollection;
        } else {
            return {};
        }
    },

    updateHiddenInputs: function(event) {
        var owner = self.options.filter == undefined ? 'all' : self.options.filter;

        if (event && event.added) {
            event.added.owner = true;
            owner = 'owner';
            self.getCollection('owner').add(event.added);
        }
        else if (event && event.removed) {
            self.getCollection(owner).remove(event.removed);
        }

        $(self.options.tagInputId + '_owner').val(self.getCollection('owner').pluck('id'));
        $(self.options.tagInputId + '_all').val(self.getCollection().pluck('id'));

        $(self.options.tagInputId).select2('data', self.getCollection(owner).toArray());
    },

    /**
     * Render widget
     *
     * @returns {}
     */
    render: function() {
        this.updateHiddenInputs();

        var tagCollection = new Oro.Tags.TagCollection();
        tagCollection.add(this.getCollection().models);
        tagCollection.add(this.getCollection('owner').models);

        $('.select2-search-choice div').click(function(){
            var tagName = $(this).attr('title');
            var tag = tagCollection.toArray().filter(function(item){ return item.name == tagName })
            var url = tag[0].url;

            if (Oro.hashNavigationEnabled()) {
                Oro.hashNavigationInstance.setLocation(url);
            } else {
                window.location = url;
            }
        });

        return this;
    }
});

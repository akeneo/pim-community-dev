Oro = Oro || {};
Oro.Tags = Oro.Tags || {};

Oro.Tags.TagCollection = Backbone.Collection.extend({
    model: Oro.Tags.Tag,

    /**
     * Return filtered collection
     *
     * @param type
     * @returns {Oro.Tags.TagCollection}
     */
    getFilteredCollection: function(type) {
        var filtered = this.filter(function(tag) {
            if (type == "owner") {
                return tag.get("owner");
            }

            return true;
        });

        return new Oro.Tags.TagCollection(filtered);
    },

    /**
     * Used for adding item on tag_update view
     * @param {Object} value
     */
    addItem: function(value) {
        var tag = new this.model({id: value.id, name: value.name, owner: true, notSaved: true});

        this.add(tag);
    }
});

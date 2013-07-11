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

    toArray: function() {
        var tagArray = [];
        _.each(this.models, function(tag) {
            tagArray.push(tag.attributes);
        });

        return tagArray;
    }
});

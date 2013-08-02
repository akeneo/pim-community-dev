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
     *
     * @param {Object} value
     */
    addItem: function(value) {
        // check if exists tag
        var exist = this.where({name: value.name});
        if (exist.length && exist[0].get('owner') == false) {
            // adding to owner
            exist[0].set('owner', true);
            this.trigger('add');

            return;
        }

        var tag = new this.model({id: value.id, name: value.name, owner: true, notSaved: true});

        this.add(tag);
    },

    removeItem: function(id, filterState) {
        var model = this.where({'id': id});
        if (model.length) {
            model = model[0];
            if (filterState == 'owner' && model.owner && model.moreOwners === false) {
                model.owner = false;

                this.trigger('remove');

                return;
            }
            this.remove(model);
        }
    }
});

/* global define */
define(['backbone', 'oro/tag/model'],
function(Backbone, TagModel) {
    'use strict';

    /**
     * @export  oro/tag/collection
     * @class   oro.tag.Collection
     * @extends Backbone.Collection
     */
    var TagCollection = Backbone.Collection.extend({
        model: TagModel,

        /**
         * Return filtered collection
         *
         * @param type
         * @returns {oro.tag.Collection}
         */
        getFilteredCollection: function(type) {
            var filtered = this.filter(function(tag) {
                return type === "owner" ? tag.get("owner") : true;
            });

            return new TagCollection(filtered);
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

            this.add(new this.model({
                id: value.id,
                name: value.name,
                owner: true,
                notSaved: true
            }));
        },

        /**
         * Remove item from collection, or uncheck "owner" if filter is not in global mdoe
         *
         * @param {string|number} id
         * @param {string} filterState
         */
        removeItem: function(id, filterState) {
            var model = this.where({'id': id});
            if (model.length) {
                model = model[0];
                if (filterState === 'owner' && model.get('owner') === true && model.get('moreOwners') === true) {
                    model.set('owner', false);

                    this.trigger('remove');

                    return;
                }
                this.remove(model);
            }
        }
    });

    return TagCollection;
});

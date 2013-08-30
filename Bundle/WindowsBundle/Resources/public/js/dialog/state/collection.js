/* global define */
define(['backbone', 'oro/dialog/state/model'],
function(Backbone, StateModel) {
    'use strict';

    /**
     * @export  oro/dialog/state/collection
     * @class   oro.dialog.state.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        model: StateModel,

        url: function() {
            return this.model.prototype.urlRoot;
        }
    });
});
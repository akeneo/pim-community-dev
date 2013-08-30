var Oro = Oro || {};
Oro.widget = Oro.widget || {};

Oro.widget.StateCollection = Backbone.Collection.extend({
    model: Oro.widget.StateModel,

    url: function() {
        return this.model.prototype.urlRoot;
    }
});

Oro.widget.States = new Oro.widget.StateCollection();
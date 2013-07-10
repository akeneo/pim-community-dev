Oro = Oro || {};
Oro.Tags = Oro.Tags || {};

Oro.Tags.Tag = Backbone.Model.extend({
    defaults: {
        owner  : false,
        url    : '',
        name   : ''
    }
});

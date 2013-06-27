var navigation = navigation || {};

navigation.Item = Backbone.Model.extend({
    defaults: {
        title: '',
        title_rendered: '',
        url: null,
        position: null,
        type: null
    },

    url: function(a) {
        var base = _.result(this, 'urlRoot') || _.result(this.collection, 'url') || urlError();
        base +=  (base.charAt(base.length - 1) === '/' ? '' : '/') + this.get('type');
        if (this.isNew()) {
            return base;
        }
        return base + (base.charAt(base.length - 1) === '/' ? '' : '/') + 'ids/' + encodeURIComponent(this.id);
    }
});

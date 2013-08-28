var Oro = Oro || {};

Oro.PageState = Oro.PageState || {};

Oro.PageState.Model = Backbone.Model.extend({
    defaults: {
        restore   : false,
        pagestate : {
            pageId : '',
            data   : {}
        }
    },

    url: function(method) {
        return this.id
            ? Routing.generate('oro_api_put_pagestate', { id: this.id })
            : Routing.generate('oro_api_post_pagestate');
    }
});

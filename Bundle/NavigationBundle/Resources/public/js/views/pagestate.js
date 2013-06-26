var Oro = Oro || {};

Oro.PageState = Oro.PageState || {};

Oro.PageState.View = Backbone.View.extend({

    timer: '',

    initialize: function () {
        this.init();
        this.listenTo(this.model, 'change:pagestate', this.handleStateChange);

        /**
         * Init page state after hash navigation request is completed
         */
        Oro.Events.bind(
            "hash_navigation_content:refresh",
            function() {
                this.init();
            },
            this
        );
        /**
         * Clear page state timer after hash navigation request is started
         */
        Oro.Events.bind(
            "hash_navigation_request:start",
            function() {
                this.clearTimer();
            },
            this
        );
    },

    init: function() {
        var self = this;

        if (Backbone.$('form[data-collect=true]').length == 0) {
            return;
        }

        Backbone.$.get(
            Routing.generate('oro_api_get_pagestate_checkid') + '?pageId=' + this.filterUrl(),
            function (data) {
                self.model.set({
                    id        : data.id,
                    pagestate : data.pagestate
                });

                if ( parseInt(data.id) > 0  && self.model.get('restore')) {
                    self.restore();
                }

                self.timer = setInterval(function() {
                    self.collect();
                }, 2000);
            }
        )
    },

    clearTimer: function() {
        clearInterval(this.timer);
        this.model.set('restore', false);
    },

    handleStateChange: function() {
        if (this.model.get('pagestate').pageId) {
            this.model.save(this.model.get('pagestate'));
        }
    },

    collect: function() {
        var filterUrl = this.filterUrl();
        if (!filterUrl) {
            return;
        }
        var data = {};

        Backbone.$('form[data-collect=true]').each(function(index, el){
            data[index] = Backbone.$(el)
                .find('input, textarea, select')
                .not(':input[type=button], :input[type=submit], :input[type=reset], :input[type=password], :input[type=file]')
                .serializeArray();
        });

        this.model.set({
            pagestate: {
                pageId : filterUrl,
                data   : JSON.stringify(data)
            }
        });
    },

    restore: function() {
        Backbone.$.each(JSON.parse(this.model.get('pagestate').data), function(index, el) {
            form = Backbone.$('form[data-collect=true]').eq(index);
            form.find('option').prop('selected', false);

            Backbone.$.each(el, function(i, input){
                element = form.find('[name="'+ input.name+'"]');
                switch (element.prop('type')) {
                    case 'checkbox':
                        element.filter('[value="'+ input.value +'"]').prop('checked', true);
                        break;
                    case 'select-multiple':
                        element.find('option[value="'+ input.value +'"]').prop('selected', true);
                        break;
                    default:
                        element.val(input.value);
                }
            });
        });
    },

    filterUrl: function() {
        var self = this;
        var url = window.location;
        if (Oro.hashNavigationEnabled()) {
            url = new Url( Oro.Navigation.prototype.getHashUrl());
            url.search = url.query.toString();
            url.pathname = url.path;
        }

        var params = url.search.replace('?', '').split('&');

        if (params.length == 1 && params[0].indexOf('restore') !== -1) {
            params = '';
            self.model.set('restore', true);
        } else {
            params = Backbone.$.grep(params, function(el) {
                if (el.indexOf('restore') == -1) {
                    return true;
                } else {
                    self.model.set('restore', true);
                    return false;
                }
            })
        }

        return base64_encode(url.pathname + (params != '' ? '?' + params.join('&') : ''));
    }
});

$(function() {
    Oro.pagestate = new Oro.PageState.View({ model: new Oro.PageState.Model });
})

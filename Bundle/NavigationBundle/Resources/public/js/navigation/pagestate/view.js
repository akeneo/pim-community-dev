/* jshint browser:true */
/* global define, require, base64_encode */
define(['underscore', 'backbone', 'url', 'routing', 'oro/navigation', 'oro/mediator', 'base64', 'json'],
function(_, Backbone, Url, routing, Navigation, mediator) {
    'use strict';

    var pageStateTimer;

    /**
     * @export  oro/navigation/pagestate/view
     * @class   oro.navigation.pagestate.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /**
         * A flag whether we need to restore data from server
         */
        needServerRestore: true,

        stopCollecting: false,

        initialize: function () {
            this.init();
            this.listenTo(this.model, 'change:pagestate', this.handleStateChange);

            /**
             * Init page state after hash navigation request is completed
             */
            mediator.bind(
                "hash_navigation_request:refresh",
                function() {
                    this.stopCollecting = false;
                    this.init();
                },
                this
            );
            /**
             * Clear page state timer after hash navigation request is started
             */
            mediator.bind(
                "hash_navigation_request:start",
                function() {
                    this.stopCollecting = true;
                    this.clearTimer();
                },
                this
            );
        },

        hasForm: function() {
            return Backbone.$('form[data-collect=true]').length;
        },

        init: function() {
            this.clearTimer();
            if (!this.hasForm()) {
                return;
            }

            Backbone.$.get(
                routing.generate('oro_api_get_pagestate_checkid') + '?pageId=' + this.filterUrl(),
                _.bind(function (data) {
                    this.clearTimer();
                    this.model.set({
                        id        : data.id,
                        pagestate : data.pagestate
                    });

                    if (parseInt(data.id) > 0  && this.model.get('restore') && this.needServerRestore) {
                        this.restore();
                    }

                    pageStateTimer = setInterval(_.bind(this.collect, this), 2000);
                }, this)
            )
        },

        clearTimer: function() {
            if (pageStateTimer) {
                clearInterval(pageStateTimer);
            }
            this.model.set('restore', false);
        },

        handleStateChange: function() {
            if (this.model.get('pagestate').pageId) {
                this.model.save(this.model.get('pagestate'));
            }
        },

        collect: function() {
            if (!this.hasForm() || this.stopCollecting) {
                this.clearTimer();
                return;
            }
            var filterUrl = this.filterUrl();
            if (!filterUrl) {
                return;
            }
            var data = {};

            Backbone.$('form[data-collect=true]').each(function(index, el){
                data[index] = Backbone.$(el)
                    .find('input, textarea, select')
                    .not(':input[type=button], :input[type=submit], :input[type=reset], :input[type=password], :input[type=file], :input[name$="[_token]"]')
                    .serializeArray();
            });

            this.model.set({
                pagestate: {
                    pageId : filterUrl,
                    data   : JSON.stringify(data)
                }
            });
            mediator.trigger("pagestate_collected", this.model);
        },

        updateState: function(data) {
            this.model.set({
                pagestate: {
                    pageId : '',
                    data   : data
                }
            });
        },

        restore: function() {
            Backbone.$.each(JSON.parse(this.model.get('pagestate').data), function(index, el) {
                var form = Backbone.$('form[data-collect=true]').eq(index);
                form.find('option').prop('selected', false);

                Backbone.$.each(el, function(i, input){
                    var element = form.find('[name="'+ input.name+'"]');
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
            mediator.trigger("pagestate_restored");
        },

        filterUrl: function() {
            var self = this,
                url = window.location,
                // cause that's circular dependency
                navigation = require('oro/navigation').getInstance();
            if (navigation) {
                url = new Url(navigation.getHashUrl());
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
                });
            }

            return base64_encode(url.pathname + (params != '' ? '?' + params.join('&') : ''));
        }
    });
});

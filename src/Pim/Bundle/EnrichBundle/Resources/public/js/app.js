'use strict';

define(function (require) {
    var $ = require('jquery');
    var Backbone = require('backbone');
    var messenger = require('oro/messenger');
    var _ = require('underscore');

    return (function () {
        return {
            debug: false,
            bootstrap: function (options) {
                this.debug = !!options.debug;
                require('pim/router');
                require('pim/page-title');

                messenger.setup({
                    container: '#flash-messages .flash-messages-holder',
                    template: _.template($.trim($('#message-item-template').html()))
                });

                // temp
                require(['pim/init'], function (init) {
                    init();
                });

                if (!Backbone.History.started) {
                    Backbone.history.start();
                }
            }
        };
    })();
});

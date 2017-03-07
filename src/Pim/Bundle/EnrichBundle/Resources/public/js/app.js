'use strict';

define([
        'jquery',
        'backbone',
        'oro/messenger',
        'underscore',
        'pim/fetcher-registry',
        'pim/init',
        'oro/init-user',
        'oro/init-layout',
        'pimuser/js/init-signin',
        'pim/router',
        'pim/page-title'
    ], function (
        $,
        Backbone,
        messenger,
        _,
        FetcherRegistry,
        init,
        initUser,
        initLayout,
        initSignin
    ) {
    return (function () {
        return {
            debug: false,
            bootstrap: function (options) {
                initUser();
                initLayout();
                initSignin();
                this.debug = !!options.debug;

                FetcherRegistry.initialize();

                messenger.setup({
                    container: '#flash-messages .flash-messages-holder',
                    template: _.template($.trim($('#message-item-template').html()))
                });

                init();

                if (!Backbone.History.started) {
                    Backbone.history.start();
                }
            }
        };
    })();
});

'use strict';

define(['pim-router', 'oro/translator'], function (router, __) {
    var routeParams = {};

    router.on('route_complete', function (name) {
        document.title = __('page_title.' + name, routeParams);
    });

    return {
        set: function (params) {
            routeParams = params;
        }
    };
});

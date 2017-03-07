'use strict';

define(['pim/router', 'underscore'], function (router, _) {
    var routeParams = {};

    router.on('route_complete', function (name) {
        document.title = _.__('page_title.' + name, routeParams);
    });

    return {
        set: function (params) {
            routeParams = params;
        }
    };
});

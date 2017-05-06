'use strict';

define(['oro/translator'], function (__) {
    var routeParams = {};
    // 'pim/router',
    // router.on('route_complete', function (name) {
    document.title = __('page_title.' + '', routeParams);
    // });

    return {
        set: function (params) {
            routeParams = params;
        }
    };
});

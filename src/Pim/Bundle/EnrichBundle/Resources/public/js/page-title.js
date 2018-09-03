'use strict';

define(['pim/router', 'oro/translator'], function (router, __) {
    let routeParams = {};
    let render = (name, params) => {
        document.title = __('pim_title.' + name, params);
    };

    router.on('route_complete', (name) => {
        render(name, routeParams);
    });

    return {
        set: (params) => {
            routeParams = params;
        },

        render: render
    };
});

'use strict';

define(function (require) {
    var router = require('pim/router');
    var _ = require('underscore');
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

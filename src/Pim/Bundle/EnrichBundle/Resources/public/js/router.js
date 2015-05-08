'use strict';

define(function (require) {
    var $ = require('jquery');
    var Backbone = require('backbone');
    var Routing = require('routing');

    return Backbone.Router.extend({
        routes: {
            '*route': 'defaultRoute'
        },
        defaultRoute: function (route) {
            if ('' === route || Routing.getBaseUrl().replace('/', '') === route.replace('/', '')) {
                route = Routing.generate('pim_dashboard_index').substring(1);
            }
            console.log('Loading', route);
            $.get('/' + route).done(function (template) {
                $('#container').html(template);

                // temp
                _.each($('a[href]'), function (link) {
                    var href = $(link).attr('href');
                    if (href.substring(0, 1) !== '#' && href.substring(0, 11) !== 'javascript:') {
                        href = '#' + href;
                    }
                    $(link).attr('href', href);
                });

            }).fail(function () {
                $('#container').html('Whoops, no such page!');
            });
        }
    });
});

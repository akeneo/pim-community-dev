'use strict';

define(
    ['backbone', 'routing'],
    function (Backbone, Routing) {
        var UserContext = Backbone.Model.extend({
            url: Routing.generate('pim_user_user_rest_get')
        });

        var instance = new UserContext();

        instance.fetch({async: false});

        return instance;
    }
);

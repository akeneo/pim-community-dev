'use strict';

define(
    ['backbone', 'routing'],
    function (Backbone, Routing) {
        var SecurityContext = Backbone.Model.extend({
            url: Routing.generate('pim_user_security_rest_get'),
            isGranted: function (acl) {
                return true === this.get(acl);
            }
        });

        var instance = new SecurityContext();

        instance.fetch({async: false});

        return instance;
    }
);

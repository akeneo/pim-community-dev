'use strict';

define(
    ['backbone', 'routing'],
    function (Backbone, Routing) {
        var SecurityContext = Backbone.Model.extend({
            url: Routing.generate('pim_user_security_rest_get'),
            isGranted: function (acl) {
                return this.get(acl) === true;
            }
        });

        var instance = new SecurityContext();

        instance.fetch({async: false});

        return instance;
    }
);

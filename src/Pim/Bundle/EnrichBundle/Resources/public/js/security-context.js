'use strict';

define(
    [
        'backbone',
        'routing',
        'pim/common/property'
    ],
    function (
        Backbone,
        Routing,
        PropertyAccessor
    ) {
        var SecurityContext = Backbone.Model.extend({
            url: Routing.generate('pim_user_security_rest_get'),
            isGranted: function (acl) {
                return this.get(acl) === true;
            },
            isConditionalGranted: function (aclArray, model) {
                if (aclArray) {
                    for (var i = 0; i < aclArray.length; i++) {
                        var acl = aclArray[i];
                        var property = PropertyAccessor.accessProperty(model, acl.propertyPath);
                        if (property === acl.value) {

                            return this.get(acl.id);
                        }
                    }
                }

                return true;
            }
        });

        var instance = new SecurityContext();

        instance.fetch({async: false});

        return instance;
    }
);

'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'pim/field-manager',
        'pimee/permission-manager',
        'oro/mediator'
    ],
    function ($, _, Backbone, BaseForm, FieldManager, PermissionManager, mediator) {
        return BaseForm.extend({
            configure: function () {
                mediator.off(null, null, 'context:product:form:attribute:read-only-locale');
                mediator.on(
                    'field:extension:add',
                    _.bind(this.addExtension, this),
                    'context:product:form:attribute:read-only-locale'
                );

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            addExtension: function (event) {
                event.promises.push(
                    PermissionManager.getPermissions().done(_.bind(function (permissions) {
                        var deferred = $.Deferred();
                        var field = event.field;

                        if (field.attribute.localizable) {
                            var localePermission = _.findWhere(permissions.locales, {code: field.context.locale});

                            if (!localePermission.edit) {
                                field.setEditable(false);
                            }
                        }
                        deferred.resolve();

                        return deferred.promise();
                    }, this))
                );

                return this;
            }
        });
    }
);

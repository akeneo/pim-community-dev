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
                mediator.on('field:extension:add', _.bind(this.addExtension, this));

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            addExtension: function (event) {
                PermissionManager.getPermissions().done(_.bind(function (permissions) {
                    var field = event.field;

                    if (field.attribute.localizable) {
                        var localePermission = _.findWhere(permissions.locales, {code: field.context.locale});

                        if (!localePermission.edit) {
                            field.setEnabled(false);
                        }
                    }
                }, this));

                return this;
            }
        });
    }
);

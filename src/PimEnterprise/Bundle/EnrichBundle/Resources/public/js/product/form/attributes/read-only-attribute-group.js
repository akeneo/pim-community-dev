'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'pim/field-manager',
        'pimee/permission-manager',
        'oro/mediator',
        'text!pimee/template/product/tab/attribute/read-only-attribute-group'
    ],
    function (_, Backbone, BaseForm, FieldManager, PermissionManager, mediator, readOnlyTemplate) {
        return BaseForm.extend({
            template: _.template(readOnlyTemplate),
            configure: function() {
                mediator.on('field:extension:add', _.bind(this.addExtension, this));

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            addExtension: function (event) {
                PermissionManager.getPermissions().done(_.bind(function (permissions) {
                    var field = event.field;
                    var attributeGroupPermission = _.findWhere(
                        permissions.attribute_groups,
                        {code: field.attribute.group}
                    );

                    if (!attributeGroupPermission.edit) {
                        field.setEnabled(false);
                    }
                }, this));

                return this;
            }
        });
    }
);

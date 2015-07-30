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
                this.listenTo(mediator, 'pim_enrich:form:field:extension:add', this.addExtension);

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            addExtension: function (event) {
                event.promises.push(
                    PermissionManager.getPermissions().done(_.bind(function (permissions) {
                        var deferred = $.Deferred();
                        var field = event.field;
                        /* jshint sub:true */
                        /* jscs:disable requireDotNotation */
                        var attributeGroupPermission = _.findWhere(
                            permissions['attribute_groups'],
                            {code: field.attribute.group}
                        );

                        if (!attributeGroupPermission.edit) {
                            field.setEditable(false);
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

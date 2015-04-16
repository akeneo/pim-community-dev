 'use strict';

define(
    [
        'jquery',
        'underscore',
        'pim/config-manager',
        'pim/permission-manager',
        'pimenrich/js/product/form/attributes'
    ],
    function ($, _, ConfigManager, PermissionManager, AttributesForm) {
        var renderField = AttributesForm.prototype.renderField;

        AttributesForm.prototype.renderField = function (product, attributeCode) {
            var promise = $.Deferred();

            $.when(
                renderField.apply(this, arguments),
                ConfigManager.getEntityList('attributes'),
                PermissionManager.getPermissions()
            ).done(_.bind(function (field, attributes, permissions) {
                var attribute = _.findWhere(attributes, {code: attributeCode});
                /* jscs:disable requireCamelCaseOrUpperCaseIdentifiers */
                var editGranted = _.findWhere(permissions.attribute_groups, {code: attribute.group}).edit;
                /* jscs:enable requireCamelCaseOrUpperCaseIdentifiers */

                if (attribute.localizable && editGranted) {
                    editGranted = _.findWhere(permissions.locales, {code: this.getLocale()}).edit;
                }

                field.setEnabled(editGranted);

                promise.resolve(field);
            }, this));

            return promise.promise();
        };

        return AttributesForm;
    }
);

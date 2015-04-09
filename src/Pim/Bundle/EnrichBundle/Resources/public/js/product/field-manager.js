'use strict';

define(
    ['jquery', 'pim/config-manager', 'pim/form-config-provider'],
    function ($, ConfigManager, ConfigProvider) {
        var fields = {};
        var loadedModules = {};
        var getFieldForAttribute = function (attribute) {
            var promise = $.Deferred();

            if (loadedModules[attribute.type]) {
                promise.resolve(loadedModules[attribute.type]);

                return promise.promise();
            }

            ConfigProvider.getAttributeFields().done(function (attributeFields) {
                var fieldModule = attributeFields[attribute.type];

                if (!fieldModule) {
                    throw new Error('No field defined for attribute type "' + attribute.type + '"');
                }

                require([fieldModule], function (Field) {
                    loadedModules[attribute.type] = Field;
                    promise.resolve(Field);
                });
            });

            return promise.promise();
        };

        return {
            getField: function (attributeCode) {
                var promise = $.Deferred();

                if (fields[attributeCode]) {
                    promise.resolve(fields[attributeCode]);

                    return promise.promise();
                }

                ConfigManager.getEntity('attributes', attributeCode).done(function (attribute) {
                    getFieldForAttribute(attribute).done(function (Field) {
                        fields[attributeCode] = new Field(attribute);
                        promise.resolve(fields[attributeCode]);
                    });
                });

                return promise.promise();
            },
            getFields: function () {
                return fields;
            }
        };
    }
);

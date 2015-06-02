'use strict';

define(
    ['jquery', 'underscore', 'pim/entity-manager', 'pim/form-config-provider'],
    function ($, _, EntityManager, ConfigProvider) {
        var fields = {};
        var loadedModules = {};
        var getFieldForAttribute = function (attribute) {
            var deferred = $.Deferred();

            if (loadedModules[attribute.type]) {
                deferred.resolve(loadedModules[attribute.type]);

                return deferred.promise();
            }

            ConfigProvider.getAttributeFields().done(function (attributeFields) {
                var fieldModule = attributeFields[attribute.type];

                if (!fieldModule) {
                    throw new Error('No field defined for attribute type "' + attribute.type + '"');
                }

                require([fieldModule], function (Field) {
                    loadedModules[attribute.type] = Field;
                    deferred.resolve(Field);
                });
            });

            return deferred.promise();
        };

        return {
            getField: function (attributeCode) {
                var deferred = $.Deferred();

                if (fields[attributeCode]) {
                    deferred.resolve(fields[attributeCode]);

                    return deferred.promise();
                }

                EntityManager.getRepository('attribute').find(attributeCode).done(function (attribute) {
                    getFieldForAttribute(attribute).done(function (Field) {
                        fields[attributeCode] = new Field(attribute);
                        deferred.resolve(fields[attributeCode]);
                    });
                });

                return deferred.promise();
            },
            getNotReadyFields: function () {
                var notReadyFields = [];

                _.each(fields, function (field) {
                    if (!field.getReady()) {
                        notReadyFields.push(field);
                    }
                });

                return notReadyFields;
            },
            getFields: function () {
                return fields;
            },
            clear: function () {
                fields = {};
            }
        };
    }
);

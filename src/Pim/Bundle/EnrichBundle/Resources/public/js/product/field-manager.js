'use strict';
/**
 * Field manager
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    ['jquery', 'underscore', 'pim/entity-manager', 'pim/form-config-provider'],
    function ($, _, EntityManager, ConfigProvider) {
        var fields = {};
        var visibleFields = {};
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
                    if (!field.isReady()) {
                        notReadyFields.push(field);
                    }
                });

                return notReadyFields;
            },
            getFields: function () {
                return fields;
            },
            addVisibleField: function (attributeCode) {
                visibleFields[attributeCode] = fields[attributeCode];
            },
            getVisibleField: function (attributeCode) {
                return visibleFields[attributeCode];
            },
            clearFields: function () {
                fields = {};
            },
            clearVisibleFields: function () {
                visibleFields = {};
            }
        };
    }
);

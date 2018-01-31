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
    ['jquery', 'underscore', 'pim/fetcher-registry', 'pim/form-config-provider', 'require-context'],
    function ($, _, FetcherRegistry, ConfigProvider, requireContext) {
        var fields = {};
        var visibleFields = {};
        var loadedModules = {};

        /**
         * Create a field view for the given attribute
         *
         * @param {Object} attribute
         *
         * @return {View}
         */
        var getFieldForAttribute = function (attribute) {
            var deferred = $.Deferred();

            if (loadedModules[attribute.field_type]) {
                deferred.resolve(loadedModules[attribute.field_type]);

                return deferred.promise();
            }

            ConfigProvider.getAttributeFields().done(function (attributeFields) {
                var fieldModule = attributeFields[attribute.field_type];

                if (!fieldModule) {
                    throw new Error('No field defined for attribute type "' + attribute.field_type + '"');
                }

                var ResolvedModule = requireContext(fieldModule);
                loadedModules[attribute.field_type] = ResolvedModule;
                deferred.resolve(ResolvedModule)
            });

            return deferred.promise();
        };

        return {
            /**
             * Get the field view for the given attribute code
             *
             * @param {string} attributeCode
             *
             * @return {View}
             */
            getField: function (attributeCode) {
                var deferred = $.Deferred();

                if (fields[attributeCode]) {
                    deferred.resolve(fields[attributeCode]);

                    return deferred.promise();
                }

                FetcherRegistry.getFetcher('attribute').fetch(attributeCode).done(function (attribute) {
                    getFieldForAttribute(attribute).done(function (Field) {
                        fields[attributeCode] = new Field(attribute);
                        deferred.resolve(fields[attributeCode]);
                    });
                });

                return deferred.promise();
            },

            /**
             * Get all the fields that are not ready (for example media fields that are currently uploading)
             *
             * @return {array}
             */
            getNotReadyFields: function () {
                return Object.values(fields).filter(field => !field.isReady());
            },

            /**
             * Get all the cached fields
             *
             * @return {array}
             */
            getFields: function () {
                return fields;
            },

            /**
             * Add a field to the collection of currently displayed fields
             *
             * @param {string} attributeCode
             */
            addVisibleField: function (attributeCode) {
                visibleFields[attributeCode] = fields[attributeCode];
            },

            /**
             * Get all visible fields
             *
             * @return {[type]}
             */
            getVisibleFields: function () {
                return visibleFields;
            },

            /**
             * Clear the field collection
             */
            clearFields: function () {
                fields = {};
                this.clearVisibleFields();
            },

            /**
             * Clear the displayed field collection
             */
            clearVisibleFields: function () {
                visibleFields = {};
            }
        };
    }
);

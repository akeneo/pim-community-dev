'use strict';

define(['jquery'], function ($) {
    const loadConfig = function () {
        const deferred = $.Deferred();
        const formExtensions = __moduleConfig;
        deferred.resolve(formExtensions);
        console.log('config', formExtensions);

        return deferred.promise();
    };

    return {
        /**
             * Returns configuration for extensions.
             *
             * @return {Promise}
             */
        getExtensionMap: function () {
            return loadConfig().then(function (config) {
                return Object.values(config.extensions);
            });
        },

        /**
             * Returns configuration for attribute fields.
             *
             * @return {Promise}
             */
        getAttributeFields: function () {
            return loadConfig().then(function (config) {
                return config.attribute_fields;
            });
        },

        /**
             * Clear cache of form registry
             */
        clear: function () {

        }
    };
});

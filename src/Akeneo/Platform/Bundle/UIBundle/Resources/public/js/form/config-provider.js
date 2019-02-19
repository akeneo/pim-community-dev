'use strict';

define(
    ['jquery', 'underscore', 'routing'],
    function ($, _, Routing) {
        var promise = null;

        var loadConfig = function () {
            if (null === promise) {
                promise = $.get('/js/extensions.json').fail(() => {
                    throw Error('It seems that your web server is not well configured as we were not able ' +
                        'to load the frontend configuration. The most likely reason is that the mod_rewrite ' +
                        'module is not installed/enabled.')
                });
            }

            return promise;
        };

        return {
            /**
             * Returns configuration for extensions.
             *
             * @return {Promise}
             */
            getExtensionMap: function () {
                return loadConfig().then(function (config) {
                    console.log(Object.values(config.extensions))
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
                promise = null;
            }
        };
    }
);

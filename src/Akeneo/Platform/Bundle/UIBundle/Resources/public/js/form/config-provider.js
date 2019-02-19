'use strict';

define(
    ['jquery', 'underscore', 'pim/security-context'],
    function ($, _, SecurityContext) {
        var promise = null;

        const filterByGranted = (extensions) => {
            const filtered = _.filter(extensions, extension => {
                return null === extension.aclResourceId || SecurityContext.isGranted(extension.aclResourceId)
            });

            return filtered
        }

        var loadConfig = function () {
            if (null === promise) {
                promise = $.when(
                    $.get('/js/extensions.json'),
                    SecurityContext.initialize()
                ).then((formExtensions) => {
                    const test = formExtensions[0]
                    test.extensions = filterByGranted(test.extensions);

                    return test;
                }).fail(() => {
                    throw Error('It seems that your web server is not well configured as we were not able to load the frontend configuration. The most likely reason is that the mod_rewrite module is not installed/enabled.')
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

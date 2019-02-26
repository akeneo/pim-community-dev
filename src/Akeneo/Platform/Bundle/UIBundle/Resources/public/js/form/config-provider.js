'use strict';

define(
    ['jquery', 'pim/security-context'],
    function ($, SecurityContext) {
        var promise = null;

        /**
         * Filters form extensions by ACL
         *
         * @param {Object} extensions
         */
        const filterByGranted = (extensions) => {
            return extensions.filter(extension => {
                return null === extension.aclResourceId || SecurityContext.isGranted(extension.aclResourceId)
            })
        }

        const loadConfig = function () {
            if (null === promise) {
                promise = $.when(
                    $.get('/js/extensions.json'),
                    SecurityContext.initialize()
                )
                .then(([config]) => {
                    config.extensions = filterByGranted(config.extensions)

                    return config;
                })
                .fail(() => {
                    throw Error(`It seems that your web server is not well
                     configured as we were not able to load the frontend
                      configuration. The most likely reason is that the
                       mod_rewrite module is not installed/enabled.`)
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
            getExtensionMap() {
                return loadConfig().then(({extensions}) => extensions)
            },

            /**
             * Returns configuration for attribute fields.
             *
             * @return {Promise}
             */
            getAttributeFields() {
                return loadConfig().then(({attribute_fields}) => attribute_fields)
            },

            /**
             * Clear cache of form registry
             */
            clear() {
                promise = null;
            }
        };
    }
);

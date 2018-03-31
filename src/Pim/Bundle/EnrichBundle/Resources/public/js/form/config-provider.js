'use strict';

define(['jquery', 'underscore', 'pim/security-context'], function ($, _, SecurityContext) {
    let runningPromise = null;

    const filterByGranted = function(extensions) {
        return _.filter(extensions, extension => {
            return null === extension.aclResourceId || SecurityContext.isGranted(extension.aclResourceId)
        });
    }

    const loadConfig = function () {
        const formExtensions = __moduleConfig;

        if (null === runningPromise) {
            runningPromise = SecurityContext.initialize().then(() => {
                formExtensions.extensions = filterByGranted(formExtensions.extensions);

                return formExtensions;
            });
        }

        return runningPromise;
    };

    return {
        /**
             * Returns configuration for extensions.
             *
             * @return {Promise}
             */
        getExtensionMap: function () {
            return loadConfig().then(config => Object.values(config.extensions));
        },

        /**
             * Returns configuration for attribute fields.
             *
             * @return {Promise}
             */
        getAttributeFields: function () {
            return loadConfig().then(config => config.attribute_fields);
        },

        /**
             * Clear cache of form registry
             */
        clear: function () {
            runningPromise = null;
        }
    };
});

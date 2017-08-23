'use strict';

define(
    ['jquery', 'underscore', 'routing'],
    function ($, _, Routing) {
        var promise = null;

        var loadConfig = function () {
            if (null === promise) {
                promise = $.getJSON(Routing.generate('pim_enrich_form_extension_rest_index')).promise();
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
                    return _.values(config.extensions);
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

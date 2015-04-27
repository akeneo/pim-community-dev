'use strict';

define(
    ['jquery', 'routing'],
    function ($, Routing) {
        var promise = null;

        var loadConfig = function () {
            if (null === promise) {
                promise = $.getJSON(Routing.generate('pim_enrich_form_extension_rest_index')).promise();
            }

            return promise;
        };

        return {
            getExtensionMap: function () {
                return loadConfig().then(function (config) {
                    return config.extensions;
                });
            },
            getAttributeFields: function () {
                return loadConfig().then(function (config) {
                    return config.attribute_fields;
                });
            }
        };
    }
);

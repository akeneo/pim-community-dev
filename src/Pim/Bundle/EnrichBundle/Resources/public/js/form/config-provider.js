'use strict';

define(
    ['jquery', 'routing'],
    function($, Routing) {
        var promise = null;

        var loadConfig = function () {
            if (null !== promise) {
                return promise.promise();
            }

            promise = $.Deferred();

            $.getJSON(Routing.generate('pim_enrich_form_extension_rest_index')).done(function (config) {
                config = config;
                promise.resolve(config);
            });

            return promise.promise();
        };

        return {
            getExtensionMap: function () {
                var promise = $.Deferred();

                loadConfig().done(function (config) {
                    promise.resolve(config.extensions);
                });

                return promise.promise();
            },
            getAttributeFields: function () {
                var promise = $.Deferred();

                loadConfig().done(function (config) {
                    promise.resolve(config.attribute_fields);
                });

                return promise.promise();
            }
        };
    }
);

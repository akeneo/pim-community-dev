'use strict';

define(
    ['jquery', 'underscore', 'routing'],
    function($, _, Routing) {
        var extensionMap = [];

        var getExtensionMap = function () {
            var promise = $.Deferred();

            if (extensionMap.length) {
                promise.resolve(extensionMap);
            } else {
                $.getJSON(Routing.generate('pim_enrich_form_extension_rest_index')).done(function (data) {
                    extensionMap = data;
                    promise.resolve(extensionMap);
                });
            }

            return promise.promise();
        };

        var getExtensionMeta = function (formName) {
            var promise = $.Deferred();

            getExtensionMap().done(function (extensionMap) {
                var form = _.first(_.where(extensionMap, { module: formName }));
                var meta = {
                    zones: form.zones,
                    extensions: _.where(extensionMap, { parent: form.code })
                };

                promise.resolve(meta);
            });

            return promise.promise();
        };

        return {
            getForm: function getForm (formName) {
                var promise = $.Deferred();

                require([formName], function(Form) {
                    promise.resolve(Form);
                });

                return promise.promise();
            },
            getFormExtensions: function getFormExtensions (formName) {
                var promise = $.Deferred();

                getExtensionMeta(formName).done(function (extensionMeta) {
                    promise.resolve(extensionMeta);
                });

                return promise.promise();
            }
        };
    }
);

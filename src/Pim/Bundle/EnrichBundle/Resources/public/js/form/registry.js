'use strict';

define(
    ['jquery', 'underscore', 'pim/form-config-provider'],
    function($, _, ConfigProvider) {
        var getExtensionMeta = function (formName) {
            var promise = $.Deferred();

            ConfigProvider.getExtensionMap().done(function (extensionMap) {
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

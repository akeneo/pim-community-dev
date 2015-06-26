'use strict';

define(
    ['jquery', 'underscore', 'pim/form-config-provider'],
    function ($, _, ConfigProvider) {
        var getExtensionMeta = function (formName) {
            return ConfigProvider.getExtensionMap().then(function (extensionMap) {
                var form = _.first(_.where(extensionMap, { module: formName }));
                var meta = {
                    extensions: _.where(extensionMap, { parent: form.code })
                };

                return meta;
            });
        };

        return {
            getForm: function getForm(formName) {
                var deferred = $.Deferred();

                require([formName], function (Form) {
                    deferred.resolve(Form);
                });

                return deferred.promise();
            },
            getFormExtensions: getExtensionMeta
        };
    }
);

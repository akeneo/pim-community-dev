'use strict';

define(
    ['jquery', 'underscore', 'pim/form-config-provider'],
    function ($, _, ConfigProvider) {
        var getForm = function (formName) {
            return ConfigProvider.getExtensionMap().then(function (extensionMap) {
                var form     = _.first(_.where(extensionMap, { code: formName }));
                var deferred = new $.Deferred();

                require([form.module], function (Form) {
                    deferred.resolve(Form);
                });

                return deferred.promise();
            });
        };

        var getExtensionMeta = function (formName) {
            return ConfigProvider.getExtensionMap().then(function (extensionMap) {
                var form = _.first(_.where(extensionMap, { code: formName }));

                return _.where(extensionMap, { parent: form.code });
            });
        };

        return {
            getForm: getForm,
            getFormExtensions: getExtensionMeta
        };
    }
);

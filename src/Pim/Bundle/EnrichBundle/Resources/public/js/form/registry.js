'use strict';

define(
    ['jquery', 'underscore', 'pim/form-config-provider'],
    function ($, _, ConfigProvider) {
        var getForm = function (formName) {
            return ConfigProvider.getExtensionMap().then(function (extensionMap) {
                var form     = _.first(_.where(extensionMap, { code: formName }));
                var deferred = new $.Deferred();

                if (undefined === form) {
                    throw new Error(
                        'The form ' + formName + ' was not found. Are you sure you registered it properly?'
                    );
                }

                require([form.module], function (Form) {
                    deferred.resolve(Form);
                });

                return deferred.promise();
            });
        };

        var getExtensionMeta = function (formName) {
            return ConfigProvider.getExtensionMap().then(function (extensionMap) {
                return _.where(extensionMap, { parent: formName });
            });
        };

        var getFormMeta = function (formName) {
            return ConfigProvider.getExtensionMap().then(function (extensionMap) {
                return _.findWhere(extensionMap, { code: formName });
            });
        };

        return {
            getForm: getForm,
            getFormExtensions: getExtensionMeta,
            getFormMeta: getFormMeta
        };
    }
);

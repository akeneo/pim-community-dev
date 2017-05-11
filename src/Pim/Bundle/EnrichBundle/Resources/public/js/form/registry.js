'use strict';

define(
    ['jquery', 'underscore', 'pim/form-config-provider', 'paths'],
    function ($, _, ConfigProvider, paths) {
        var getForm = function (formName) {
            return ConfigProvider.getExtensionMap().then(function (extensionMap) {
                var form     = _.first(_.where(extensionMap, { code: formName }));
                var deferred = new $.Deferred();

                if (undefined === form) {
                    throw new Error(
                        'The form ' + formName + ' was not found. Are you sure you registered it properly?'
                    );
                }

                require.ensure([], function() {
                    var requestFetcher = require.context('./src/Pim/Bundle', true, /^\.\/.*\.js$/)
                    console.log(paths[form.module])
                    var ResolvedModule = requestFetcher(paths[form.module]);
                    deferred.resolve(ResolvedModule)
                })

                // require([form.module], function (Form) {
                //     deferred.resolve(Form);
                // });

                return deferred.promise();
            });
        };

        var getExtensionMeta = function (formName) {
            return ConfigProvider.getExtensionMap().then(function (extensionMap) {
                var form = _.findWhere(extensionMap, { code: formName });
                var extensions = _.where(extensionMap, { parent: form.code });

                return $.extend(true, {}, extensions);
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

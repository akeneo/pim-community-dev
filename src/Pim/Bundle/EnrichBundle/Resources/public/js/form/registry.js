'use strict';

define(
    ['jquery', 'underscore', 'pim/form-config-provider', 'paths', 'require-context'],
    function ($, _, ConfigProvider, paths, requireContext) {
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
                    // var formPath = paths[form.module]
                    // var formContext = './src/Pim/Bundle'
                    //
                    // // Make an async fetcher module and put this kind of logic in there, load it from webpack
                    //
                    // if (formPath.indexOf('oroconfig') > -1) {
                    //     formContext = './src/Oro/Bundle'
                    // }
                    // var requestFetcher = require.context(formContext, true, /^\.\/.*\.js$/)
                    var ResolvedModule = requireContext(form.module);
                    deferred.resolve(ResolvedModule)
                })

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

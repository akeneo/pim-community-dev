'use strict';

define(
    ['jquery', 'underscore', 'pim/form-registry', 'require-context'],
    function ($, _, FormRegistry, requireContext) {
        var buildForm = function (formName) {
            return $.when(
                FormRegistry.getFormMeta(formName),
                FormRegistry.getFormExtensions(formName)
            ).then(function (formMeta, extensionMeta) {
                const Form = requireContext(formMeta.module);
                const form = new Form(formMeta);
                form.code = formName;

                const extensionPromises = extensionMeta.map((extension) => {
                    return buildForm(extension.code).then(function (loadedModule) {
                        extension.loadedModule = loadedModule;

                        return extension;
                    });
                });

                return $.when.apply($, extensionPromises).then(function () {
                    extensionMeta.forEach((extension) => {
                        form.addExtension(
                            extension.code,
                            extension.loadedModule,
                            extension.targetZone,
                            extension.position
                        );
                    });

                    return form;
                });
            });
        };

        return {
            build: function (formName) {
                return buildForm(formName).then(function (form) {
                    return form.configure().then(function () {
                        return form;
                    });
                });
            },

            buildForm: buildForm
        };
    }
);

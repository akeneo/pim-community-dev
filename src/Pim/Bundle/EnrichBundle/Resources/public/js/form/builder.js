'use strict';

define(
    ['jquery', 'underscore', 'pim/form-registry', 'require-context'],
    function ($, _, FormRegistry, requireContext) {
        var buildForm = function (formName) {
            return FormRegistry.getFormMeta(formName).then((formMeta) => {
                if (undefined === formMeta) {
                    throw new Error(`
                        The form ${formName} was not found. Are you sure you registered it properly?
                        Check your form_extension files and be sure to clear your prod cache before proceeding
                    `);
                }

                return FormRegistry.getFormExtensions(formMeta).then((extensionsMeta) => {
                    const FormClass = requireContext(formMeta.module);

                    if (typeof FormClass !== 'function') {
                        throw new Error(`Your module ${formMeta.module} must return a function. It returns: ${typeof FormClass}`);
                    }

                    const form = new FormClass(formMeta);
                    form.code = formName;

                    const extensionPromises = extensionsMeta.map((extension) => {
                        return buildForm(extension.code).then(function (loadedModule) {
                            extension.loadedModule = loadedModule;

                            return extension;
                        });
                    });

                    return $.when.apply($, extensionPromises).then(function () {
                        extensionsMeta.forEach((extension) => {
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

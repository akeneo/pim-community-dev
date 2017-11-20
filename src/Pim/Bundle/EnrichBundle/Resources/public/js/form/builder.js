'use strict';

define(
    ['jquery', 'underscore', 'pim/form-registry', 'require-context'],
    function ($, _, FormRegistry, requireContext) {
        const getFormMeta = function (formName) {
            return FormRegistry.getFormMeta(formName).then((formMeta) => {
                if (undefined === formMeta) {
                    throw new Error(`
                        The extension ${formName} was not found. Are you sure you registered it properly?
                        Check your form_extension files and be sure to clear your prod cache before proceeding
                    `);
                }

                return formMeta;
            });
        };

        const buildForm = function (formMeta) {
            return FormRegistry.getFormExtensions(formMeta).then((extensionsMeta) => {
                const FormClass = requireContext(formMeta.module);

                if (typeof FormClass !== 'function') {
                    throw new Error(`
                        The extension ${formMeta.module} must return a function.
                        It returns: ${typeof FormClass}`
                    );
                }

                const form = new FormClass(formMeta);
                form.code = formMeta.code;

                const extensionPromises = extensionsMeta.map((extension) => {
                    return getFormMeta(extension.code)
                        .then(buildForm)
                        .then(function (loadedModule) {
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
        };

        return {
            getFormMeta: getFormMeta,
            buildForm: buildForm,
            build: function (formName) {
                return getFormMeta(formName)
                    .then(buildForm)
                    .then(function (form) {
                        return form.configure().then(function () {
                            return form;
                        });
                    });
            }
        };
    }
);

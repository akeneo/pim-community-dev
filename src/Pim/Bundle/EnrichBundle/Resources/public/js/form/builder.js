'use strict';

define(
    ['jquery', 'underscore', 'pim/form-registry', 'require-context'],
    function ($, _, FormRegistry, requireContext) {
        const getFormMeta = function (formName) {
            return FormRegistry.getFormMeta(formName).then((formMeta) => {
                if (undefined === formMeta) {
                    throw new Error(`
The extension "${formName}" was not found. Are you sure you registered it properly?
Check your form_extension files and be sure to clear your prod cache before proceeding
                    `);
                }

                return formMeta;
            });
        };

        const buildForm = function (formMeta) {
            return FormRegistry.getFormExtensions(formMeta).then((extensionsMeta) => {
                const FormClass = requireContext(formMeta.module);

                if (undefined === FormClass) {
                    throw new Error(`
The module "${formMeta.module}" is undefined.
Most of the time it's because it's not well registered in your requirejs.yml file.
Here is the documentation to fix this problem
https://docs.akeneo.com/latest/design_pim/overview.html#register-it`
                    );
                }

                if (typeof FormClass !== 'function') {
                    throw new Error(`
                        The module "${formMeta.module}" must return a function.
                        It returns: ${typeof FormClass}`
                    );
                }

                const form = new FormClass(formMeta);
                form.code = formMeta.code;

                const filteredExtensionsMeta = extensionsMeta.filter(
                    (extensionsMeta) => null !== extensionsMeta.module
                );

                const extensionPromises = filteredExtensionsMeta.map((extension) => {
                    return getFormMeta(extension.code)
                        .then(buildForm)
                        .then(function (loadedModule) {
                            extension.loadedModule = loadedModule;

                            return extension;
                        });
                });

                return $.when.apply($, extensionPromises).then(function () {
                    filteredExtensionsMeta.forEach((extension) => {
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
                        const promise = form.configure();

                        if (undefined === promise || typeof promise.then !== 'function') {
                            throw new Error(`
The method configure for the module "${form.code}" must return a promise.
If you get this error, it often means that you forgot to return:
BaseForm.prototype.configure.apply(this, arguments)
(See details here: https://docs.akeneo.com/latest/design_pim/overview.html#useful-methods)
                            `);
                        }

                        return promise.then(function () {
                            return form;
                        });
                    });
            }
        };
    }
);

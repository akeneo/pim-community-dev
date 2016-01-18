'use strict';

define(
    ['jquery', 'underscore', 'pim/form-registry'],
    function ($, _, FormRegistry) {
        var buildForm = function (formName) {
            return $.when(
                FormRegistry.getForm(formName),
                FormRegistry.getFormExtensions(formName)
            ).then(function (Form, extensionMeta) {
                var form = new Form();
                form.code = formName;

                var extensionPromises = [];
                _.each(extensionMeta, function (extension) {
                    var extensionPromise = buildForm(extension.code);
                    extensionPromise.done(function (loadedModule) {
                        extension.loadedModule = loadedModule;
                    });

                    extensionPromises.push(extensionPromise);
                });

                return $.when.apply($, extensionPromises).then(function () {
                    _.each(extensionMeta, function (extension) {
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
            }
        };
    }
);

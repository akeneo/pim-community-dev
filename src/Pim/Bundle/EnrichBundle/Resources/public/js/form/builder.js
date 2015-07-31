'use strict';

define(
    ['jquery', 'underscore', 'pim/form-registry'],
    function ($, _, FormRegistry) {
        var buildForm = function (formName) {
            var deferred = $.Deferred();

            $.when(
                FormRegistry.getForm(formName),
                FormRegistry.getFormExtensions(formName)
            ).done(function (Form, extensionMeta) {
                var form = new Form();

                var extensionPromises = [];
                _.each(extensionMeta.extensions, function (extension) {
                    var extensionPromise = buildForm(extension.module);
                    extensionPromise.done(function (loadedModule) {
                        extension.loadedModule = loadedModule;
                    });

                    extensionPromises.push(extensionPromise);
                });

                $.when.apply($, extensionPromises).done(function () {
                    _.each(extensionMeta.extensions, function (extension) {
                        form.addExtension(
                            extension.code,
                            extension.loadedModule,
                            extension.targetZone,
                            extension.position
                        );
                    });

                    deferred.resolve(form);
                });
            });

            return deferred.promise();
        };

        return {
            build: function (formName) {
                return buildForm(formName).done(function (form) {
                    return form.configure().done(function () {
                        return form;
                    });
                });
            }
        };
    }
);

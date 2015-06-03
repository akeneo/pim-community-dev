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

                form.setZones(extensionMeta.zones);

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
                            extension.insertAction,
                            extension.position
                        );
                    });

                    deferred.resolve(form);
                });
            });

            return deferred.promise();
        };

        return {
            build: function buildRootForm(formName) {
                var deferred = $.Deferred();

                buildForm(formName).done(function (form) {
                    form.configure().done(function () {
                        deferred.resolve(form);
                    });
                });

                return deferred.promise();
            }
        };
    }
);

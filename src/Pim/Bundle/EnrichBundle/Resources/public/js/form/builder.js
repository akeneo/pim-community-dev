'use strict';

define(
    ['jquery', 'underscore', 'pim/form-registry'],
    function($, _, FormRegistry) {
        var buildForm = function  (formName) {
            var promise = $.Deferred();

            $.when(
                FormRegistry.getForm(formName),
                FormRegistry.getFormExtensions(formName)
            ).done(function (Form, extensions) {
                var form = new Form();

                var extensionPromises = [];
                _.each(extensions, function(extension) {
                    var extensionPromise = buildForm(extension.module);
                    extensionPromise.done(function(loadedExtension) {
                        form.addExtension(extension.code, loadedExtension);
                    });

                    extensionPromises.push(extensionPromise);
                });

                $.when.apply($, extensionPromises).done(function() {
                    promise.resolve(form);
                });
            });

            return promise.promise();
        };

        return {
            build: function buildRootForm (formName) {
                var promise = $.Deferred();

                buildForm(formName).done(function(form) {
                    form.configure().done(function () {
                        promise.resolve(form);
                    });
                });

                return promise.promise();
            }
        };
    }
);

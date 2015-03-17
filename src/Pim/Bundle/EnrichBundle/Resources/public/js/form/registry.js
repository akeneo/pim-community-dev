'use strict';

define(
    ['jquery'],
    function($) {
        var extensionMap = {
            'pim/product-edit-form': [
                {
                    'code': 'save',
                    'module': 'pim/product-edit-form/save'
                },
                {
                    'code': 'form-tabs',
                    'module': 'pim/product-edit-form/form-tabs'
                },
                {
                    'code': 'locale-switcher',
                    'module': 'pim/product-edit-form/locale-switcher'
                }
            ],
            'pim/product-edit-form/form-tabs': [
                {
                    'code': 'attributes',
                    'module': 'pim/product-edit-form/attributes'
                },
                {
                    'code': 'categories',
                    'module': 'pim/product-edit-form/categories'
                },
                {
                    'code': 'panels',
                    'module': 'pim/product-edit-form/panel/panels'
                }
            ],
            'pim/product-edit-form/attributes': [
                {
                    'code': 'scope-switcher',
                    'module': 'pim/product-edit-form/scope-switcher'
                },
                {
                    'code': 'copy',
                    'module': 'pim/product-edit-form/attributes/copy'
                }
            ],
            'pim/product-edit-form/panel/panels': [
                {
                    'code': 'completeness',
                    'module': 'pim/product-edit-form/panel/completeness'
                },
                {
                    'code': 'selector',
                    'module': 'pim/product-edit-form/panel/selector'
                }
            ]
        };

        var getExtensions = function (formName) {
            return extensionMap[formName] || [];
        };

        return {
            getForm: function getForm (formName) {
                var promise = $.Deferred();

                require([formName], function(Form) {
                    promise.resolve(Form);
                });

                return promise.promise();
            },
            getFormExtensions: function getFormExtensions (formName) {
                var promise = $.Deferred();

                var extensions = getExtensions(formName);

                var requirePromises = [];
                _.each(extensions, function (extension) {
                    var requirePromise = $.Deferred();
                    require([extension.module], function() {
                        requirePromise.resolve(extension);
                    });

                    requirePromises.push(requirePromise);
                });

                $.when.apply($, requirePromises).done(function() {
                    promise.resolve(extensions);
                });

                return promise.promise();
            }

        };
    }
);

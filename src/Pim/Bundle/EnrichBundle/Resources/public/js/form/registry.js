'use strict';

define(
    ['jquery'],
    function($) {
        var extensionMap = {
            'pim/product-edit-form': [
                'pim/product-edit-form/save',
                'pim/product-edit-form/form-tabs',
                'pim/product-edit-form/locale-switcher'
            ],
            'pim/product-edit-form/form-tabs': [
                'pim/product-edit-form/attributes',
                'pim/product-edit-form/categories',
                'pim/product-edit-form/panel/panels'
            ],
            'pim/product-edit-form/attributes': [
                'pim/product-edit-form/scope-switcher'
            ],
            'pim/product-edit-form/panel/panels': [
                'pim/product-edit-form/panel/completeness'
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

                require(extensions, function() {
                    promise.resolve(extensions);
                });

                return promise.promise();
            }

        };
    }
);

'use strict';

define(
    ['jquery', 'underscore'],
    function($, _) {
        var extensionMap = {
            'pim/product-edit-form': {
                'extensions': [
                    {
                        'code': 'save',
                        'module': 'pim/product-edit-form/save',
                        'zone': 'buttons',
                        'insertAction': 'append'
                    },
                    {
                        'code': 'form-tabs',
                        'module': 'pim/product-edit-form/form-tabs',
                        'zone': 'header',
                        'insertAction': 'after'
                    }
                ],
                'zones': {
                    'header': '>div>header',
                    'title': 'header .product-title',
                    'buttons': 'header .actions'
                }
            },
            'pim/product-edit-form/form-tabs': {
                'extensions' : [
                    {
                        'code': 'attributes',
                        'module': 'pim/product-edit-form/attributes',
                        'zone': 'container',
                        'insertAction': 'append'
                    },
                    {
                        'code': 'categories',
                        'module': 'pim/product-edit-form/categories',
                        'zone': 'container',
                        'insertAction': 'append'
                    },
                    {
                        'code': 'panels',
                        'module': 'pim/product-edit-form/panel/panels',
                        'zone': 'container',
                        'insertAction': 'append'
                    }
                ],
                'zones': {
                    'container': '.form-container'
                }
            },
            'pim/product-edit-form/attributes': {
                'extensions': [
                    {
                        'code': 'attribute-group-selector',
                        'module': 'pim/product-edit-form/attributes/attribute-group-selector',
                        'zone': 'attributes',
                        'insertAction': 'prepend'
                    },
                    {
                        'code': 'add-attribute',
                        'module': 'pim/product-edit-form/attributes/add-attribute',
                        'zone': 'edit-actions',
                        'insertAction': 'append'
                    },
                    {
                        'code': 'scope-switcher',
                        'module': 'pim/product-edit-form/scope-switcher',
                        'zone': 'edit-actions',
                        'insertAction': 'prepend'
                    },
                    {
                        'code': 'locale-switcher',
                        'module': 'pim/product-edit-form/locale-switcher',
                        'zone': 'edit-actions',
                        'insertAction': 'prepend'
                    },
                    {
                        'code': 'copy',
                        'module': 'pim/product-edit-form/attributes/copy',
                        'zone': 'header',
                        'insertAction': 'append'
                    },
                    {
                        'code': 'validation',
                        'module': 'pim/product-edit-form/attributes/validation',
                        'zone': 'header',
                        'insertAction': 'append'
                    }
                ],
                'zones': {
                    'header' : '.tab-content > header',
                    'edit-actions': '.tab-content > header > .attribute-edit-actions',
                    'attributes': 'self'
                }
            },
            'pim/product-edit-form/panel/panels': {
                'extensions': [
                    {
                        'code': 'completeness',
                        'module': 'pim/product-edit-form/panel/completeness'
                    },
                    {
                        'code': 'history',
                        'module': 'pim/product-edit-form/panel/history'
                    },
                    {
                        'code': 'selector',
                        'module': 'pim/product-edit-form/panel/selector'
                    }
                ],
                'zones': {}
            },
            'pim/product-edit-form/attributes/copy': {
                'extensions': [
                    {
                        'code': 'scope-switcher',
                        'module': 'pim/product-edit-form/scope-switcher',
                        'zone': 'copy-actions',
                        'insertAction': 'prepend'
                    },
                    {
                        'code': 'locale-switcher',
                        'module': 'pim/product-edit-form/locale-switcher',
                        'zone': 'copy-actions',
                        'insertAction': 'prepend'
                    }
                ],
                'zones': {
                    'copy-actions' : '.copy-actions'
                }
            }
        };

        var getExtensionMeta = function (formName) {
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

                var extensionMeta = getExtensionMeta(formName);

                require(_.pluck(extensionMeta.extensions, 'module'), function() {
                    promise.resolve(extensionMeta);
                });

                return promise.promise();
            }

        };
    }
);

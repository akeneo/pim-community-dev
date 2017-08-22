'use strict';

define(
    ['jquery', 'pim/form-config-provider', 'require-context'],
    function ($, ConfigProvider, requireContext) {
        const getFormExtensions = (formName) => {
            return $.when(
                ConfigProvider.getExtensionMap(),
                getFormMeta(formName)
            ).then((extensionMap, form) => {
                if (undefined === form) {
                    throw new Error(
                        `The form ${formName} was not found. Are you sure you registered it properly?`
                    );
                }

                return extensionMap.filter(extension => extension.parent === form.code);
            });
        };

        const getFormMeta = (formName) => {
            return ConfigProvider.getExtensionMap().then((extensionMap) => {
                const form = extensionMap.find(extension => extension.code === formName);

                return form;
            });
        };

        return {
            getFormExtensions,
            getFormMeta
        };
    }
);

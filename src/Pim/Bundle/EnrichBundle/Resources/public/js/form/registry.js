'use strict';

define(
    ['jquery', 'pim/form-config-provider'],
    function ($, ConfigProvider) {
        const getFormExtensions = (formMeta) => {
            return ConfigProvider.getExtensionMap().then((extensionMap) => {
                return extensionMap.filter(extension => extension.parent === formMeta.code);
            });
        };

        const getFormMeta = (formName) => {
            return ConfigProvider.getExtensionMap().then((extensionMap) => {
                return extensionMap.find(extension => extension.code === formName);
            });
        };

        return {
            getFormExtensions,
            getFormMeta
        };
    }
);

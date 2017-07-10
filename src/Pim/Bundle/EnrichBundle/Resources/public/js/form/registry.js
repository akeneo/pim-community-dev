

import $ from 'jquery';
import _ from 'underscore';
import ConfigProvider from 'pim/form-config-provider';
import requireContext from 'require-context';
var getForm = function (formName) {
    return ConfigProvider.getExtensionMap().then(function (extensionMap) {
        var form     = _.first(_.where(extensionMap, { code: formName }));
        var deferred = new $.Deferred();

        if (undefined === form) {
            throw new Error(
                        'The form ' + formName + ' was not found. Are you sure you registered it properly?'
                    );
        }

        var ResolvedModule = requireContext(form.module);
        deferred.resolve(ResolvedModule)

        return deferred.promise();
    });
};

var getExtensionMeta = function (formName) {
    return ConfigProvider.getExtensionMap().then(function (extensionMap) {
        var form = _.findWhere(extensionMap, { code: formName });
        var extensions = _.where(extensionMap, { parent: form.code });

        return $.extend(true, {}, extensions);
    });
};

var getFormMeta = function (formName) {
    return ConfigProvider.getExtensionMap().then(function (extensionMap) {
        return _.findWhere(extensionMap, { code: formName });
    });
};

export default {
    getForm: getForm,
    getFormExtensions: getExtensionMeta,
    getFormMeta: getFormMeta
};


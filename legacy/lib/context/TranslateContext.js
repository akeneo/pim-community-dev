import React, { useContext } from 'react';
var defaultTranslator = function (id) { return "translation_from_legacy." + id; };
var TranslateContext = React.createContext(defaultTranslator);
var useTranslate = function () {
    var translator = useContext(TranslateContext);
    if (!translator) {
        throw new Error('[DependenciesContext]: Translate has not been properly initiated');
    }
    return translator;
};
var TranslateProvider = function (_a) {
    var value = _a.value, children = _a.children;
    return React.createElement(TranslateContext.Provider, { value: value }, children);
};
export { useTranslate, TranslateContext, TranslateProvider };
//# sourceMappingURL=TranslateContext.js.map
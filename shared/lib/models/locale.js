var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var denormalizeLocale = function (locale) {
    if (!isLocale(locale)) {
        throw new Error('Invalid locale');
    }
    return __assign({}, locale);
};
var isLocales = function (locales) {
    if (!Array.isArray(locales)) {
        return false;
    }
    return !locales.some(function (locale) {
        return !isLocale(locale);
    });
};
var isLocale = function (locale) {
    return 'string' === typeof locale.code &&
        'string' === typeof locale.label &&
        'string' === typeof locale.region &&
        'string' === typeof locale.language;
};
var createLocaleFromCode = function (code) {
    if ('string' !== typeof code) {
        throw new Error("CreateLocaleFromCode expects a string as parameter (" + typeof code + " given)");
    }
    var _a = code.split('_'), language = _a[0], region = _a[1];
    return {
        code: code,
        label: code,
        region: region.toLowerCase(),
        language: language,
    };
};
var localeExists = function (locales, currentLocale) {
    return locales.some(function (_a) {
        var code = _a.code;
        return code === currentLocale;
    });
};
export { createLocaleFromCode, denormalizeLocale, localeExists, isLocales, isLocale };
//# sourceMappingURL=locale.js.map
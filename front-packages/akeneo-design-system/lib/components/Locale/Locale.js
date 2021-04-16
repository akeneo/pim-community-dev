"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
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
var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Locale = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var shared_1 = require("../../shared");
var theme_1 = require("../../theme");
var LocaleContainer = styled_components_1.default.span(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: inline-flex;\n  align-items: baseline;\n"], ["\n  display: inline-flex;\n  align-items: baseline;\n"])));
var Emoji = styled_components_1.default.span(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  font-size: ", ";\n  margin-right: 5px;\n"], ["\n  font-size: ", ";\n  margin-right: 5px;\n"])), theme_1.getFontSize('bigger'));
var Locale = react_1.default.forwardRef(function (_a, forwardedRef) {
    var code = _a.code, languageLabel = _a.languageLabel, rest = __rest(_a, ["code", "languageLabel"]);
    var _b = code.split('_'), languageCode = _b[0], length = _b.length, _c = length - 1, countryCode = _b[_c];
    return (react_1.default.createElement(LocaleContainer, __assign({ ref: forwardedRef }, rest),
        react_1.default.createElement(Emoji, { role: "img", "aria-label": countryCode }, shared_1.getEmoji(countryCode)),
        languageLabel || languageCode));
});
exports.Locale = Locale;
var templateObject_1, templateObject_2;
//# sourceMappingURL=Locale.js.map
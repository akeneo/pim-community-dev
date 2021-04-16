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
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
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
exports.HighlightTitle = exports.Information = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var theme_1 = require("../../theme");
var Container = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  align-items: center;\n  display: flex;\n  font-weight: 400;\n  padding-right: 15px;\n  color: ", ";\n  min-height: 100px;\n  background-color: ", ";\n"], ["\n  align-items: center;\n  display: flex;\n  font-weight: 400;\n  padding-right: 15px;\n  color: ", ";\n  min-height: 100px;\n  background-color: ", ";\n"])), theme_1.getColor('grey120'), theme_1.getColor('blue10'));
var IconContainer = styled_components_1.default.span(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  height: 80px;\n  padding: 0px 20px 0px 20px;\n  margin: 20px 15px 20px 0px;\n  border-right: 1px solid ", ";\n"], ["\n  height: 80px;\n  padding: 0px 20px 0px 20px;\n  margin: 20px 15px 20px 0px;\n  border-right: 1px solid ", ";\n"])), theme_1.getColor('grey80'));
var HelperTitle = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  color: ", ";\n  font-size: ", ";\n  font-weight: 700;\n"], ["\n  color: ", ";\n  font-size: ", ";\n  font-weight: 700;\n"])), theme_1.getColor('grey140'), theme_1.getFontSize('bigger'));
var ContentContainer = styled_components_1.default.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  padding: 10px 0px 10px 0px;\n"], ["\n  padding: 10px 0px 10px 0px;\n"])));
var Information = react_1.default.forwardRef(function (_a, forwardedRef) {
    var illustration = _a.illustration, title = _a.title, children = _a.children, rest = __rest(_a, ["illustration", "title", "children"]);
    var resizedIllustration = react_1.isValidElement(illustration) && react_1.default.cloneElement(illustration, { size: 80 });
    return (react_1.default.createElement(Container, __assign({ ref: forwardedRef }, rest),
        react_1.default.createElement(IconContainer, null, resizedIllustration),
        react_1.default.createElement(ContentContainer, null,
            react_1.default.createElement(HelperTitle, null, title),
            children)));
});
exports.Information = Information;
var HighlightTitle = styled_components_1.default.span(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  color: ", ";\n"], ["\n  color: ", ";\n"])), theme_1.getColor('brand', 100));
exports.HighlightTitle = HighlightTitle;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5;
//# sourceMappingURL=Information.js.map
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
exports.Breadcrumb = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var theme_1 = require("../../theme");
var Link_1 = require("../../components/Link/Link");
var Step = styled_components_1.default(Link_1.Link)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  text-transform: uppercase;\n  text-decoration: none;\n  color: ", ";\n"], ["\n  text-transform: uppercase;\n  text-decoration: none;\n  color: ", ";\n"])), theme_1.getColor('grey', 120));
Step.displayName = 'Breadcrumb.Step';
var BreadcrumbContainer = styled_components_1.default.nav(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  ", ":last-child {\n    color: ", ";\n    cursor: initial;\n  }\n"], ["\n  ", ":last-child {\n    color: ", ";\n    cursor: initial;\n  }\n"])), Step, theme_1.getColor('grey', 100));
var Separator = styled_components_1.default.span(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  margin: 0 0.5rem;\n"], ["\n  margin: 0 0.5rem;\n"])));
var Breadcrumb = function (_a) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    var childrenCount = react_1.default.Children.count(children);
    return (react_1.default.createElement(BreadcrumbContainer, __assign({ "aria-label": "Breadcrumb" }, rest), react_1.default.Children.map(children, function (child, index) {
        if (!(react_1.isValidElement(child) && child.type === Step)) {
            throw new Error('Breadcrumb only accepts `Breacrumb.Step` elements as children');
        }
        var isLastStep = childrenCount - 1 === index;
        return isLastStep ? (react_1.default.cloneElement(child, { 'aria-current': 'page', disabled: true })) : (react_1.default.createElement(react_1.default.Fragment, null,
            child,
            react_1.default.createElement(Separator, { "aria-hidden": true }, "/")));
    })));
};
exports.Breadcrumb = Breadcrumb;
Breadcrumb.Step = Step;
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=Breadcrumb.js.map
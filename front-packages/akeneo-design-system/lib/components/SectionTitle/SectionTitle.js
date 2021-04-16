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
exports.SectionTitle = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var theme_1 = require("../../theme");
var components_1 = require("../../components");
var SectionTitleContainer = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  width: 100%;\n  gap: 10px;\n  align-items: center;\n  height: 44px;\n  line-height: 44px;\n  border-bottom: 1px solid ", ";\n\n  ", "\n"], ["\n  display: flex;\n  width: 100%;\n  gap: 10px;\n  align-items: center;\n  height: 44px;\n  line-height: 44px;\n  border-bottom: 1px solid ", ";\n\n  ",
    "\n"])), theme_1.getColor('grey', 140), function (_a) {
    var sticky = _a.sticky;
    return undefined !== sticky && styled_components_1.css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      position: sticky;\n      top: ", "px;\n      background-color: ", ";\n      z-index: 9;\n    "], ["\n      position: sticky;\n      top: ", "px;\n      background-color: ", ";\n      z-index: 9;\n    "])), sticky, theme_1.getColor('white'));
});
var Title = styled_components_1.default.h2(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  color: ", ";\n  font-size: ", ";\n  font-weight: 400;\n  text-transform: uppercase;\n  text-overflow: ellipsis;\n  white-space: nowrap;\n  overflow: hidden;\n"], ["\n  color: ", ";\n  font-size: ", ";\n  font-weight: 400;\n  text-transform: uppercase;\n  text-overflow: ellipsis;\n  white-space: nowrap;\n  overflow: hidden;\n"])), theme_1.getColor('grey', 140), theme_1.getFontSize('big'));
var Spacer = styled_components_1.default.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  flex-grow: 1;\n"], ["\n  flex-grow: 1;\n"])));
var Separator = styled_components_1.default.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  border-left: 1px solid ", ";\n  margin: 0 10px;\n  height: 24px;\n"], ["\n  border-left: 1px solid ", ";\n  margin: 0 10px;\n  height: 24px;\n"])), theme_1.getColor('grey', 100));
var Information = styled_components_1.default.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  font-size: ", ";\n  font-weight: normal;\n  color: ", ";\n  white-space: nowrap;\n"], ["\n  font-size: ", ";\n  font-weight: normal;\n  color: ", ";\n  white-space: nowrap;\n"])), theme_1.getFontSize('default'), theme_1.getColor('brand', 100));
var SectionTitle = function (_a) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    var decoratedChildren = react_1.default.Children.map(children, function (child) {
        if (react_1.default.isValidElement(child) && child.type === components_1.IconButton) {
            return react_1.default.cloneElement(child, {
                level: 'tertiary',
                size: 'small',
                ghost: 'borderless',
            });
        }
        if (react_1.default.isValidElement(child) && child.type === components_1.Button) {
            return react_1.default.cloneElement(child, {
                level: 'tertiary',
                size: 'small',
                ghost: true,
            });
        }
        return child;
    });
    return react_1.default.createElement(SectionTitleContainer, __assign({}, rest), decoratedChildren);
};
exports.SectionTitle = SectionTitle;
SectionTitle.Title = Title;
SectionTitle.Spacer = Spacer;
SectionTitle.Separator = Separator;
SectionTitle.Information = Information;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6;
//# sourceMappingURL=SectionTitle.js.map
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
exports.Toolbar = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var theme_1 = require("../../theme");
var ToolbarContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  gap: 15px;\n  padding: 0 15px;\n  background-color: ", ";\n  align-items: center;\n  border-top: 1px solid ", ";\n  flex-basis: ", ";\n  min-height: ", ";\n  transition: flex-basis 0.3s ease-in-out, min-height 0.3s ease-in-out, border 0.3s ease-in-out;\n  overflow: ", ";\n"], ["\n  display: flex;\n  gap: 15px;\n  padding: 0 15px;\n  background-color: ", ";\n  align-items: center;\n  border-top: 1px solid ", ";\n  flex-basis: ", ";\n  min-height: ", ";\n  transition: flex-basis 0.3s ease-in-out, min-height 0.3s ease-in-out, border 0.3s ease-in-out;\n  overflow: ", ";\n"])), theme_1.getColor('white'), function (_a) {
    var isVisible = _a.isVisible;
    return (!isVisible ? 'transparent' : theme_1.getColor('grey', 80));
}, function (_a) {
    var isVisible = _a.isVisible;
    return (!isVisible ? 0 : '70px');
}, function (_a) {
    var isVisible = _a.isVisible;
    return (!isVisible ? 0 : '70px');
}, function (_a) {
    var isVisible = _a.isVisible;
    return (!isVisible ? 'hidden' : 'visible');
});
var SelectionContainer = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  gap: 10px;\n  align-items: center;\n"], ["\n  display: flex;\n  gap: 10px;\n  align-items: center;\n"])));
var ActionsContainer = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: flex;\n  gap: 10px;\n  align-items: center;\n"], ["\n  display: flex;\n  gap: 10px;\n  align-items: center;\n"])));
var LabelContainer = styled_components_1.default.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  white-space: nowrap;\n  color: ", ";\n  text-transform: uppercase;\n  font-size: ", ";\n  align-items: center;\n"], ["\n  white-space: nowrap;\n  color: ", ";\n  text-transform: uppercase;\n  font-size: ", ";\n  align-items: center;\n"])), theme_1.getColor('grey', 120), theme_1.getFontSize('default'));
var Toolbar = function (_a) {
    var _b = _a.isVisible, isVisible = _b === void 0 ? true : _b, children = _a.children, rest = __rest(_a, ["isVisible", "children"]);
    return (react_1.default.createElement(ToolbarContainer, __assign({ isVisible: isVisible }, rest), children));
};
exports.Toolbar = Toolbar;
Toolbar.LabelContainer = LabelContainer;
Toolbar.SelectionContainer = SelectionContainer;
Toolbar.ActionsContainer = ActionsContainer;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=Toolbar.js.map
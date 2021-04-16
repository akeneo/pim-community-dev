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
exports.TableRow = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var theme_1 = require("../../../theme");
var __1 = require("../..");
var SelectableContext_1 = require("../SelectableContext");
var RowContainer = styled_components_1.default.tr(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  ", ";\n\n  ", "\n\n  &:hover > td {\n    opacity: 1;\n    ", "\n  }\n\n  &:hover > td > div {\n    opacity: 1;\n  }\n"], ["\n  ",
    ";\n\n  ",
    "\n\n  &:hover > td {\n    opacity: 1;\n    ",
    "\n  }\n\n  &:hover > td > div {\n    opacity: 1;\n  }\n"])), function (_a) {
    var isSelected = _a.isSelected;
    return isSelected && styled_components_1.css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      > td {\n        background-color: ", ";\n      }\n    "], ["\n      > td {\n        background-color: ", ";\n      }\n    "])), theme_1.getColor('blue', 20));
}, function (_a) {
    var isClickable = _a.isClickable;
    return isClickable && styled_components_1.css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n      &:hover {\n        cursor: pointer;\n      }\n    "], ["\n      &:hover {\n        cursor: pointer;\n      }\n    "])));
}, function (_a) {
    var isClickable = _a.isClickable;
    return isClickable && styled_components_1.css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n        background-color: ", ";\n      "], ["\n        background-color: ", ";\n      "])), theme_1.getColor('grey', 20));
});
var CheckboxContainer = styled_components_1.default.td(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  background: none !important;\n  opacity: ", ";\n  cursor: auto;\n\n  > div {\n    justify-content: center;\n  }\n"], ["\n  background: none !important;\n  opacity: ", ";\n  cursor: auto;\n\n  > div {\n    justify-content: center;\n  }\n"])), function (_a) {
    var isVisible = _a.isVisible;
    return (isVisible ? 1 : 0);
});
var TableRow = react_1.default.forwardRef(function (_a, forwardedRef) {
    var isSelected = _a.isSelected, onSelectToggle = _a.onSelectToggle, onClick = _a.onClick, children = _a.children, rest = __rest(_a, ["isSelected", "onSelectToggle", "onClick", "children"]);
    var _b = react_1.default.useContext(SelectableContext_1.SelectableContext), isSelectable = _b.isSelectable, displayCheckbox = _b.displayCheckbox;
    if (isSelectable && (undefined === isSelected || undefined === onSelectToggle)) {
        throw Error('A row in a selectable table should have the prop "isSelected" and "onSelectToggle"');
    }
    var handleCheckboxChange = function (e) {
        e.stopPropagation();
        undefined !== onSelectToggle && onSelectToggle(!isSelected);
    };
    return (react_1.default.createElement(RowContainer, __assign({ ref: forwardedRef, isClickable: undefined !== onClick, isSelected: !!isSelected, onClick: onClick }, rest),
        isSelectable && (react_1.default.createElement(CheckboxContainer, { "aria-hidden": !displayCheckbox && !isSelected, isVisible: displayCheckbox || !!isSelected, onClick: handleCheckboxChange },
            react_1.default.createElement(__1.Checkbox, { checked: !!isSelected, onChange: function (_value, e) {
                    handleCheckboxChange(e);
                } }))),
        children));
});
exports.TableRow = TableRow;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5;
//# sourceMappingURL=TableRow.js.map
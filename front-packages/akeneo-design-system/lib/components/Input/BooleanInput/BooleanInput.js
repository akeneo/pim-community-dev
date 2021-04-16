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
Object.defineProperty(exports, "__esModule", { value: true });
exports.BooleanInput = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var theme_1 = require("../../../theme");
var icons_1 = require("../../../icons");
var BooleanInputContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject([""], [""])));
var BooleanButton = styled_components_1.default.button(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  ", "\n  height: 40px;\n  width: 60px;\n  display: inline-block;\n  line-height: 36px;\n  text-align: center;\n  vertical-align: middle;\n  overflow: hidden;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n  background: ", ";\n\n  ", ";\n"], ["\n  ", "\n  height: 40px;\n  width: 60px;\n  display: inline-block;\n  line-height: 36px;\n  text-align: center;\n  vertical-align: middle;\n  overflow: hidden;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n  background: ", ";\n\n  ",
    ";\n"])), theme_1.CommonStyle, theme_1.getColor('white'), function (_a) {
    var readOnly = _a.readOnly;
    return readOnly
        ? styled_components_1.css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n    border: 1px solid ", "}\n    color: ", "}\n  "], ["\n    border: 1px solid ", "}\n    color: ", "}\n  "])), theme_1.getColor('grey', 60), theme_1.getColor('grey', 80)) : styled_components_1.css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n    border: 1px solid ", "}\n    cursor: pointer;\n  "], ["\n    border: 1px solid ", "}\n    cursor: pointer;\n  "])), theme_1.getColor('grey', 80));
});
var NoButton = styled_components_1.default(BooleanButton)(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n  border-radius: 2px 0 0 2px;\n\n  ", "\n"], ["\n  border-radius: 2px 0 0 2px;\n\n  ",
    "\n"])), function (_a) {
    var value = _a.value, readOnly = _a.readOnly;
    return value === false
        ? styled_components_1.css(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n          background: ", ";\n          border-color: ", ";\n          color: ", ";\n        "], ["\n          background: ", ";\n          border-color: ", ";\n          color: ", ";\n        "])), theme_1.getColor('grey', readOnly ? 60 : 100), theme_1.getColor('grey', readOnly ? 60 : 100), theme_1.getColor('white')) : styled_components_1.css(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n          border-right-width: 0;\n        "], ["\n          border-right-width: 0;\n        "])));
});
var YesButton = styled_components_1.default(BooleanButton)(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n  border-radius: 0 2px 2px 0;\n\n  ", "\n"], ["\n  border-radius: 0 2px 2px 0;\n\n  ",
    "\n"])), function (_a) {
    var value = _a.value, readOnly = _a.readOnly;
    switch (value) {
        case true:
            return styled_components_1.css(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n          background: ", ";\n          border-color: ", ";\n          color: ", ";\n        "], ["\n          background: ", ";\n          border-color: ", ";\n          color: ", ";\n        "])), theme_1.getColor('green', readOnly ? 60 : 100), theme_1.getColor('green', readOnly ? 60 : 100), theme_1.getColor('white'));
        case false:
            return styled_components_1.css(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n          border-left-width: 0;\n        "], ["\n          border-left-width: 0;\n        "])));
        default:
            return '';
    }
});
var ClearButton = styled_components_1.default.button(templateObject_11 || (templateObject_11 = __makeTemplateObject(["\n  ", "\n  border: 0;\n  margin-left: 5px;\n  padding: 5px;\n  vertical-align: middle;\n  background: ", ";\n  color: ", ";\n  ", ";\n"], ["\n  ", "\n  border: 0;\n  margin-left: 5px;\n  padding: 5px;\n  vertical-align: middle;\n  background: ", ";\n  color: ", ";\n  ", ";\n"])), theme_1.CommonStyle, theme_1.getColor('white'), theme_1.getColor('grey', 100), function (_a) {
    var readOnly = _a.readOnly;
    return !readOnly && 'cursor: pointer';
});
var BooleanInputEraseIcon = styled_components_1.default(icons_1.EraseIcon)(templateObject_12 || (templateObject_12 = __makeTemplateObject(["\n  vertical-align: bottom;\n  margin-right: 6px;\n"], ["\n  vertical-align: bottom;\n  margin-right: 6px;\n"])));
var IconContainer = styled_components_1.default.span(templateObject_13 || (templateObject_13 = __makeTemplateObject(["\n  color: 1px solid ", ";\n  vertical-align: middle;\n  margin-left: 10px;\n"], ["\n  color: 1px solid ", ";\n  vertical-align: middle;\n  margin-left: 10px;\n"])), theme_1.getColor('grey', 100));
var BooleanInputLockIcon = styled_components_1.default(icons_1.LockIcon)(templateObject_14 || (templateObject_14 = __makeTemplateObject([""], [""])));
var BooleanInput = react_1.default.forwardRef(function (_a, forwardedRef) {
    var value = _a.value, readOnly = _a.readOnly, onChange = _a.onChange, _b = _a.clearable, clearable = _b === void 0 ? false : _b, yesLabel = _a.yesLabel, noLabel = _a.noLabel, clearLabel = _a.clearLabel, rest = __rest(_a, ["value", "readOnly", "onChange", "clearable", "yesLabel", "noLabel", "clearLabel"]);
    var handleChange = react_1.useCallback(function (value) {
        if (!onChange) {
            return;
        }
        onChange(value);
    }, [onChange, readOnly]);
    return (react_1.default.createElement(BooleanInputContainer, __assign({ role: "switch", "aria-checked": null === value ? undefined : value, ref: forwardedRef }, rest),
        react_1.default.createElement(NoButton, { value: value, readOnly: readOnly, "aria-readonly": readOnly, disabled: readOnly, onClick: function () {
                handleChange(false);
            }, title: noLabel }, noLabel),
        react_1.default.createElement(YesButton, { value: value, readOnly: readOnly, "aria-readonly": readOnly, disabled: readOnly, onClick: function () {
                handleChange(true);
            }, title: yesLabel }, yesLabel),
        value !== null && !readOnly && clearable && (react_1.default.createElement(ClearButton, { onClick: function () {
                handleChange(null);
            } },
            react_1.default.createElement(BooleanInputEraseIcon, { size: 16 }),
            clearLabel)),
        readOnly && (react_1.default.createElement(IconContainer, null,
            react_1.default.createElement(BooleanInputLockIcon, { size: 16 })))));
});
exports.BooleanInput = BooleanInput;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10, templateObject_11, templateObject_12, templateObject_13, templateObject_14;
//# sourceMappingURL=BooleanInput.js.map
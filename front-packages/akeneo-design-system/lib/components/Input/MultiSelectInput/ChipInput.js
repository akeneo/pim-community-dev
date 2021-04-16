"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
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
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.ChipInput = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var icons_1 = require("../../../icons");
var theme_1 = require("../../../theme");
var components_1 = require("../../../components");
var hooks_1 = require("../../../hooks");
var shared_1 = require("../../../shared");
var Container = styled_components_1.default.ul(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  border: 1px solid ", ";\n  border-radius: 2px;\n  padding: 4px 30px 4px 4px;\n  display: flex;\n  flex-wrap: wrap;\n  min-height: 40px;\n  gap: 5px;\n  box-sizing: border-box;\n  background: ", ";\n  position: relative;\n  margin: 0;\n\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n"], ["\n  border: 1px solid ", ";\n  border-radius: 2px;\n  padding: 4px 30px 4px 4px;\n  display: flex;\n  flex-wrap: wrap;\n  min-height: 40px;\n  gap: 5px;\n  box-sizing: border-box;\n  background: ", ";\n  position: relative;\n  margin: 0;\n\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n"])), function (_a) {
    var invalid = _a.invalid;
    return (invalid ? theme_1.getColor('red', 100) : theme_1.getColor('grey', 80));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? theme_1.getColor('grey', 20) : theme_1.getColor('white'));
}, theme_1.getColor('blue', 40));
var Chip = styled_components_1.default.li(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  list-style-type: none;\n  padding: 3px 15px;\n  padding-left: ", ";\n  border: 1px ", " solid;\n  background-color: ", ";\n  display: flex;\n  align-items: center;\n  height: 30px;\n  box-sizing: border-box;\n  color: ", ";\n"], ["\n  list-style-type: none;\n  padding: 3px 15px;\n  padding-left: ", ";\n  border: 1px ", " solid;\n  background-color: ", ";\n  display: flex;\n  align-items: center;\n  height: 30px;\n  box-sizing: border-box;\n  color: ", ";\n"])), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? '15px' : '4px');
}, theme_1.getColor('grey', 80), function (_a) {
    var isSelected = _a.isSelected;
    return (isSelected ? theme_1.getColor('grey', 40) : theme_1.getColor('grey', 20));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? theme_1.getColor('grey', 100) : theme_1.getColor('grey', 140));
});
var Input = styled_components_1.default.input(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  width: 100%;\n  height: 100%;\n  border: 0;\n  outline: 0;\n  color: ", ";\n  background-color: transparent;\n  font-size: ", ";\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"], ["\n  width: 100%;\n  height: 100%;\n  border: 0;\n  outline: 0;\n  color: ", ";\n  background-color: transparent;\n  font-size: ", ";\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"])), theme_1.getColor('grey', 120), theme_1.getFontSize('default'), theme_1.getColor('grey', 100));
var InputContainer = styled_components_1.default.li(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  list-style-type: none;\n  color: ", ";\n  border: 0;\n  flex: 1;\n  padding: 0;\n  align-items: center;\n  display: flex;\n\n  :first-child > ", " {\n    padding-left: 11px;\n  }\n"], ["\n  list-style-type: none;\n  color: ", ";\n  border: 0;\n  flex: 1;\n  padding: 0;\n  align-items: center;\n  display: flex;\n\n  :first-child > ", " {\n    padding-left: 11px;\n  }\n"])), theme_1.getColor('grey', 120), Input);
var ReadOnlyIcon = styled_components_1.default(icons_1.LockIcon)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 11px;\n  color: ", ";\n"], ["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 11px;\n  color: ", ";\n"])), theme_1.getColor('grey', 100));
var RemoveButton = styled_components_1.default(components_1.IconButton)(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  background-color: transparent;\n  margin-left: -3px;\n  margin-right: 1px;\n  color: ", ";\n"], ["\n  background-color: transparent;\n  margin-left: -3px;\n  margin-right: 1px;\n  color: ", ";\n"])), theme_1.getColor('grey', 100));
var ChipInput = react_1.default.forwardRef(function (_a, forwardedRef) {
    var id = _a.id, value = _a.value, invalid = _a.invalid, readOnly = _a.readOnly, placeholder = _a.placeholder, searchValue = _a.searchValue, removeLabel = _a.removeLabel, onRemove = _a.onRemove, onSearchChange = _a.onSearchChange, onFocus = _a.onFocus;
    var _b = hooks_1.useBooleanState(), isLastSelected = _b[0], selectLast = _b[1], unselectLast = _b[2];
    var handleChange = function (event) { return onSearchChange(event.target.value); };
    var handleBackspace = function () {
        if ('' !== searchValue || 0 === value.length) {
            return;
        }
        if (isLastSelected) {
            onRemove(value[value.length - 1].code);
        }
        else {
            selectLast();
        }
    };
    react_1.useEffect(function () {
        unselectLast();
    }, [value, searchValue]);
    hooks_1.useShortcut(shared_1.Key.Backspace, handleBackspace, forwardedRef);
    return (react_1.default.createElement(Container, { invalid: invalid, readOnly: readOnly },
        value.map(function (chip, index) { return (react_1.default.createElement(Chip, { key: chip.code, readOnly: readOnly, isSelected: index === value.length - 1 && isLastSelected },
            !readOnly && (react_1.default.createElement(RemoveButton, { title: removeLabel, ghost: "borderless", size: "small", level: "tertiary", icon: react_1.default.createElement(icons_1.CloseIcon, null), onClick: function () { return onRemove(chip.code); } })),
            chip.label)); }),
        react_1.default.createElement(InputContainer, null,
            react_1.default.createElement(Input, { type: "text", id: id, value: searchValue, ref: forwardedRef, placeholder: value.length === 0 ? placeholder : undefined, onChange: handleChange, onBlur: unselectLast, "aria-invalid": invalid, readOnly: readOnly, disabled: readOnly, onFocus: onFocus }),
            readOnly && react_1.default.createElement(ReadOnlyIcon, { size: 16 }))));
});
exports.ChipInput = ChipInput;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6;
//# sourceMappingURL=ChipInput.js.map
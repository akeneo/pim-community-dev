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
exports.TextAreaInput = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var icons_1 = require("../../../icons");
var theme_1 = require("../../../theme");
var RichTextEditor_1 = require("./RichTextEditor");
var TextAreaInputContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  position: relative;\n  display: flex;\n  flex-direction: column;\n  width: 100%;\n"], ["\n  position: relative;\n  display: flex;\n  flex-direction: column;\n  width: 100%;\n"])));
var CommonStyle = styled_components_1.css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  border: 1px solid ", ";\n  border-radius: 2px;\n  color: ", ";\n  font-size: ", ";\n  line-height: 20px;\n  width: 100%;\n  box-sizing: border-box;\n  font-family: inherit;\n  outline-style: none;\n  background: ", ";\n  cursor: ", ";\n\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n"], ["\n  border: 1px solid ", ";\n  border-radius: 2px;\n  color: ", ";\n  font-size: ", ";\n  line-height: 20px;\n  width: 100%;\n  box-sizing: border-box;\n  font-family: inherit;\n  outline-style: none;\n  background: ", ";\n  cursor: ", ";\n\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n"])), function (_a) {
    var invalid = _a.invalid;
    return (invalid ? theme_1.getColor('red', 100) : theme_1.getColor('grey', 80));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? theme_1.getColor('grey', 100) : theme_1.getColor('grey', 140));
}, theme_1.getFontSize('default'), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? theme_1.getColor('grey', 20) : theme_1.getColor('white'));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? 'not-allowed' : 'auto');
}, theme_1.getColor('blue', 40));
var RichTextEditorContainer = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  ", "\n  padding: 0;\n  padding-bottom: 10px;\n\n  & .rdw-editor-main {\n    min-height: 200px;\n    max-height: 400px;\n    padding: 0 30px 10px 15px;\n  }\n\n  & .rdw-option-wrapper {\n    min-width: 30px;\n    height: 30px;\n  }\n\n  & .rdw-editor-toolbar {\n    border: none;\n    padding: 0;\n    margin: 0;\n    padding: 5px 30px 0 0;\n    border-radius: 0;\n    border-bottom: 1px solid ", ";\n  }\n\n  & .rdw-dropdown-wrapper:hover,\n  & .rdw-option-wrapper:hover,\n  & .rdw-dropdown-optionwrapper:hover {\n    box-shadow: none;\n  }\n"], ["\n  ", "\n  padding: 0;\n  padding-bottom: 10px;\n\n  & .rdw-editor-main {\n    min-height: 200px;\n    max-height: 400px;\n    padding: 0 30px 10px 15px;\n  }\n\n  & .rdw-option-wrapper {\n    min-width: 30px;\n    height: 30px;\n  }\n\n  & .rdw-editor-toolbar {\n    border: none;\n    padding: 0;\n    margin: 0;\n    padding: 5px 30px 0 0;\n    border-radius: 0;\n    border-bottom: 1px solid ", ";\n  }\n\n  & .rdw-dropdown-wrapper:hover,\n  & .rdw-option-wrapper:hover,\n  & .rdw-dropdown-optionwrapper:hover {\n    box-shadow: none;\n  }\n"])), CommonStyle, function (_a) {
    var invalid = _a.invalid;
    return (invalid ? theme_1.getColor('red', 100) : theme_1.getColor('grey', 80));
});
var Textarea = styled_components_1.default.textarea(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  ", "\n  resize: none;\n  height: 200px;\n  padding: 10px 30px 10px 15px;\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"], ["\n  ", "\n  resize: none;\n  height: 200px;\n  padding: 10px 30px 10px 15px;\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"])), CommonStyle, theme_1.getColor('grey', 100));
var ReadOnlyIcon = styled_components_1.default(icons_1.LockIcon)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 12px;\n  color: ", ";\n"], ["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 12px;\n  color: ", ";\n"])), theme_1.getColor('grey', 100));
var CharacterLeftLabel = styled_components_1.default.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  font-size: ", ";\n  align-self: flex-end;\n  color: ", ";\n"], ["\n  font-size: ", ";\n  align-self: flex-end;\n  color: ", ";\n"])), theme_1.getFontSize('small'), theme_1.getColor('grey', 100));
var TextAreaInput = react_1.default.forwardRef(function (_a, forwardedRef) {
    var value = _a.value, invalid = _a.invalid, onChange = _a.onChange, readOnly = _a.readOnly, characterLeftLabel = _a.characterLeftLabel, _b = _a.isRichText, isRichText = _b === void 0 ? false : _b, richTextEditorProps = _a.richTextEditorProps, rest = __rest(_a, ["value", "invalid", "onChange", "readOnly", "characterLeftLabel", "isRichText", "richTextEditorProps"]);
    var handleChange = react_1.useCallback(function (event) {
        if (!readOnly && onChange)
            onChange(event.currentTarget.value);
    }, [readOnly, onChange]);
    return (react_1.default.createElement(TextAreaInputContainer, null,
        isRichText ? (react_1.default.createElement(RichTextEditorContainer, { readOnly: readOnly, invalid: invalid },
            react_1.default.createElement(RichTextEditor_1.RichTextEditor, __assign({ readOnly: readOnly, value: value }, richTextEditorProps, { onChange: function (value) { return onChange === null || onChange === void 0 ? void 0 : onChange(value); } })))) : (react_1.default.createElement(Textarea, __assign({ ref: forwardedRef, value: value, onChange: handleChange, type: "text", readOnly: readOnly, disabled: readOnly, "aria-invalid": invalid, invalid: invalid }, rest))),
        readOnly && react_1.default.createElement(ReadOnlyIcon, { size: 16 }),
        characterLeftLabel && react_1.default.createElement(CharacterLeftLabel, null, characterLeftLabel)));
});
exports.TextAreaInput = TextAreaInput;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6;
//# sourceMappingURL=TextAreaInput.js.map
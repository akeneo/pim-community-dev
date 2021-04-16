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
var __spreadArray = (this && this.__spreadArray) || function (to, from) {
    for (var i = 0, il = from.length, j = to.length; i < il; i++, j++)
        to[j] = from[i];
    return to;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.TagInput = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var theme_1 = require("../../../theme");
var icons_1 = require("../../../icons");
var shared_1 = require("../../../shared");
var TagInput = function (_a) {
    var onChange = _a.onChange, placeholder = _a.placeholder, invalid = _a.invalid, _b = _a.value, value = _b === void 0 ? [] : _b, readOnly = _a.readOnly, onSubmit = _a.onSubmit, inputProps = __rest(_a, ["onChange", "placeholder", "invalid", "value", "readOnly", "onSubmit"]);
    var _c = react_1.useState(false), isLastTagSelected = _c[0], setLastTagAsSelected = _c[1];
    var inputRef = react_1.useRef(null);
    var containerRef = react_1.useRef(null);
    var inputContainerRef = react_1.useRef(null);
    var onChangeCreateTags = react_1.useCallback(function (event) {
        var tagsAsString = event.currentTarget.value;
        if (tagsAsString !== '') {
            var newTags = tagsAsString.split(/[\s,;]+/);
            if (newTags.length === 1) {
                return;
            }
            var newTagsWithoutEmpty = newTags.filter(function (tag) { return tag.trim() !== ''; });
            createTags(__spreadArray(__spreadArray([], value), newTagsWithoutEmpty));
        }
    }, [value]);
    var onBlurCreateTag = react_1.useCallback(function (event) {
        var inputCurrentValue = event.currentTarget.value.trim();
        if (inputCurrentValue !== '') {
            createTags(__spreadArray(__spreadArray([], value), [inputCurrentValue]));
        }
    }, [value]);
    var createTags = react_1.useCallback(function (newTags) {
        newTags = shared_1.arrayUnique(newTags);
        onChange(newTags);
        if (inputRef && inputRef.current) {
            inputRef.current.value = '';
        }
    }, [inputRef, onChange]);
    var removeTag = react_1.useCallback(function (tagIndexToRemove) {
        var clonedTags = __spreadArray([], value);
        clonedTags.splice(tagIndexToRemove, 1);
        onChange(clonedTags);
    }, [value, onChange]);
    var focusOnInputField = react_1.useCallback(function (event) {
        if (inputRef &&
            inputRef.current &&
            ((containerRef && containerRef.current === event.target) ||
                (inputContainerRef && inputContainerRef.current === event.target))) {
            inputRef.current.focus();
        }
    }, [inputRef, containerRef, inputContainerRef]);
    var handleKeyDown = react_1.useCallback(function (event) {
        if (shared_1.Key.Enter === event.key && !isLastTagSelected && !readOnly) {
            onSubmit === null || onSubmit === void 0 ? void 0 : onSubmit();
            return;
        }
        var isDeleteKeyPressed = [shared_1.Key.Backspace.toString(), shared_1.Key.Delete.toString()].includes(event.key);
        var tagsAreEmpty = value.length === 0;
        var inputFieldIsNotEmpty = inputRef && inputRef.current && inputRef.current.value.trim() !== '';
        if (!isDeleteKeyPressed || tagsAreEmpty || inputFieldIsNotEmpty) {
            setLastTagAsSelected(false);
            return;
        }
        if (isLastTagSelected) {
            var newTags = value.slice(0, value.length - 1);
            onChange(newTags);
        }
        setLastTagAsSelected(!isLastTagSelected);
    }, [isLastTagSelected, setLastTagAsSelected, onChange, value, inputRef, onSubmit, readOnly]);
    return (react_1.default.createElement(TagContainer, { "data-testid": "tagInputContainer", ref: containerRef, invalid: invalid, onClick: focusOnInputField, readOnly: readOnly },
        value.map(function (tag, index) {
            return (react_1.default.createElement(Tag, { key: tag.toLowerCase() + "-" + index, "data-testid": "tag", isSelected: index === value.length - 1 && isLastTagSelected, readOnly: readOnly },
                !readOnly && react_1.default.createElement(RemoveTagIcon, { onClick: function () { return removeTag(index); }, "data-testid": "remove-" + index }),
                tag));
        }),
        react_1.default.createElement(InputContainer, { ref: inputContainerRef, onClick: focusOnInputField },
            react_1.default.createElement("input", __assign({ type: "text", "data-testid": "tag-input", ref: inputRef, placeholder: value.length === 0 ? placeholder : '', onKeyDown: handleKeyDown, onChange: onChangeCreateTags, onBlurCapture: onBlurCreateTag, "aria-invalid": invalid, readOnly: readOnly }, inputProps)),
            readOnly && react_1.default.createElement(ReadOnlyIcon, { size: 16 }))));
};
exports.TagInput = TagInput;
var RemoveTagIcon = styled_components_1.default(icons_1.CloseIcon)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  width: 12px;\n  height: 12px;\n  color: ", ";\n  margin-right: 2px;\n  cursor: pointer;\n"], ["\n  width: 12px;\n  height: 12px;\n  color: ", ";\n  margin-right: 2px;\n  cursor: pointer;\n"])), theme_1.getColor('grey', 120));
var TagContainer = styled_components_1.default.ul(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  border: 1px solid ", ";\n  border-radius: 2px;\n  padding: 4px;\n  display: flex;\n  flex-wrap: wrap;\n  min-height: 40px;\n  gap: 5px;\n  box-sizing: border-box;\n  background: ", ";\n  position: relative;\n  width: 100%;\n\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n"], ["\n  border: 1px solid ", ";\n  border-radius: 2px;\n  padding: 4px;\n  display: flex;\n  flex-wrap: wrap;\n  min-height: 40px;\n  gap: 5px;\n  box-sizing: border-box;\n  background: ", ";\n  position: relative;\n  width: 100%;\n\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n"])), function (_a) {
    var invalid = _a.invalid;
    return (invalid ? theme_1.getColor('red', 100) : theme_1.getColor('grey', 80));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? theme_1.getColor('grey', 20) : theme_1.getColor('white'));
}, theme_1.getColor('blue', 40));
var Tag = styled_components_1.default.li(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  list-style-type: none;\n  padding: ", ";\n  border: 1px ", " solid;\n  background-color: ", ";\n  display: flex;\n  align-items: center;\n  height: 30px;\n  box-sizing: border-box;\n"], ["\n  list-style-type: none;\n  padding: ", ";\n  border: 1px ", " solid;\n  background-color: ", ";\n  display: flex;\n  align-items: center;\n  height: 30px;\n  box-sizing: border-box;\n"])), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? '3px 17px 3px 17px' : '3px 17px 3px 4px');
}, theme_1.getColor('grey', 80), function (_a) {
    var isSelected = _a.isSelected;
    return (isSelected ? theme_1.getColor('grey', 40) : theme_1.getColor('grey', 20));
});
var InputContainer = styled_components_1.default.li(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  list-style-type: none;\n  color: ", ";\n  border: 0;\n  flex: 1;\n  padding: 0;\n  align-items: center;\n  display: flex;\n\n  > input {\n    border: 0;\n    outline: 0;\n    color: ", ";\n    background-color: transparent;\n\n    &::placeholder {\n      opacity: 1;\n      color: ", ";\n      font-family: ", ";\n    }\n  }\n"], ["\n  list-style-type: none;\n  color: ", ";\n  border: 0;\n  flex: 1;\n  padding: 0;\n  align-items: center;\n  display: flex;\n\n  > input {\n    border: 0;\n    outline: 0;\n    color: ", ";\n    background-color: transparent;\n\n    &::placeholder {\n      opacity: 1;\n      color: ", ";\n      font-family: ", ";\n    }\n  }\n"])), theme_1.getColor('grey', 120), theme_1.getColor('grey', 120), theme_1.getColor('grey', 100), theme_1.getFontFamily('default'));
var ReadOnlyIcon = styled_components_1.default(icons_1.LockIcon)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 11px;\n  color: ", ";\n"], ["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 11px;\n  color: ", ";\n"])), theme_1.getColor('grey', 100));
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5;
//# sourceMappingURL=TagInput.js.map
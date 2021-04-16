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
exports.MediaLinkInput = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var shared_1 = require("../../../shared");
var theme_1 = require("../../../theme");
var illustrations_1 = require("../../../illustrations");
var components_1 = require("../../../components");
var icons_1 = require("../../../icons");
var hooks_1 = require("../../../hooks");
var DefaultPicture_svg_1 = __importDefault(require("../../../../static/illustrations/DefaultPicture.svg"));
var MediaLinkInputContainer = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  position: relative;\n  display: flex;\n  flex-direction: row;\n  align-items: center;\n  padding: 12px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  height: 74px;\n  gap: 10px;\n  outline-style: none;\n  box-sizing: border-box;\n  background: ", ";\n  cursor: ", ";\n  overflow: hidden;\n  ", "\n"], ["\n  position: relative;\n  display: flex;\n  flex-direction: row;\n  align-items: center;\n  padding: 12px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  height: 74px;\n  gap: 10px;\n  outline-style: none;\n  box-sizing: border-box;\n  background: ", ";\n  cursor: ", ";\n  overflow: hidden;\n  ",
    "\n"])), function (_a) {
    var invalid = _a.invalid;
    return (invalid ? theme_1.getColor('red', 100) : theme_1.getColor('grey', 80));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? theme_1.getColor('grey', 20) : theme_1.getColor('white'));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? 'not-allowed' : 'auto');
}, function (_a) {
    var readOnly = _a.readOnly;
    return !readOnly && styled_components_1.css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      &:focus-within {\n        box-shadow: 0 0 0 2px ", ";\n      }\n    "], ["\n      &:focus-within {\n        box-shadow: 0 0 0 2px ", ";\n      }\n    "])), theme_1.getColor('blue', 40));
});
var Input = styled_components_1.default.input(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  border: none;\n  flex: 1;\n  outline: none;\n  color: ", ";\n  background: transparent;\n  cursor: ", ";\n  height: 100%;\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"], ["\n  border: none;\n  flex: 1;\n  outline: none;\n  color: ", ";\n  background: transparent;\n  cursor: ", ";\n  height: 100%;\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"])), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? theme_1.getColor('grey', 100) : theme_1.getColor('grey', 140));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? 'not-allowed' : 'auto');
}, theme_1.getColor('grey', 100));
var ReadOnlyIcon = styled_components_1.default(icons_1.LockIcon)(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  margin-left: 4px;\n"], ["\n  margin-left: 4px;\n"])));
var ActionContainer = styled_components_1.default.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  display: flex;\n  gap: 2px;\n  align-items: center;\n  color: ", ";\n"], ["\n  display: flex;\n  gap: 2px;\n  align-items: center;\n  color: ", ";\n"])), theme_1.getColor('grey', 100));
var MediaLinkImage = styled_components_1.default(components_1.Image)(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  border: none;\n"], ["\n  border: none;\n"])));
var MediaLinkInput = react_1.default.forwardRef(function (_a, forwardedRef) {
    var onChange = _a.onChange, value = _a.value, placeholder = _a.placeholder, thumbnailUrl = _a.thumbnailUrl, children = _a.children, _b = _a.invalid, invalid = _b === void 0 ? false : _b, _c = _a.readOnly, readOnly = _c === void 0 ? false : _c, onSubmit = _a.onSubmit, rest = __rest(_a, ["onChange", "value", "placeholder", "thumbnailUrl", "children", "invalid", "readOnly", "onSubmit"]);
    var internalRef = react_1.useRef(null);
    forwardedRef = forwardedRef !== null && forwardedRef !== void 0 ? forwardedRef : internalRef;
    var containerRef = react_1.useRef(null);
    var _d = react_1.useState(thumbnailUrl), displayedThumbnailUrl = _d[0], setDisplayedThumbnailUrl = _d[1];
    react_1.useEffect(function () {
        setDisplayedThumbnailUrl(thumbnailUrl);
    }, [thumbnailUrl]);
    var actions = react_1.default.Children.map(children, function (child) {
        if (react_1.isValidElement(child) && components_1.IconButton === child.type) {
            return react_1.cloneElement(child, {
                level: 'tertiary',
                ghost: 'borderless',
                size: 'small',
            });
        }
        return null;
    });
    var handleChange = function (event) {
        if (!readOnly && onChange)
            onChange(event.currentTarget.value);
    };
    var handleEnter = function () {
        !readOnly && (onSubmit === null || onSubmit === void 0 ? void 0 : onSubmit());
    };
    hooks_1.useShortcut(shared_1.Key.Enter, handleEnter, forwardedRef);
    return (react_1.default.createElement(react_1.default.Fragment, null,
        react_1.default.createElement(MediaLinkInputContainer, { ref: containerRef, tabIndex: readOnly ? -1 : 0, invalid: invalid, readOnly: readOnly },
            '' !== value ? (react_1.default.createElement(MediaLinkImage, { src: displayedThumbnailUrl, height: 47, width: 47, alt: value, onError: function () { return setDisplayedThumbnailUrl(DefaultPicture_svg_1.default); } })) : (react_1.default.createElement(illustrations_1.DefaultPictureIllustration, { title: placeholder, size: 47 })),
            react_1.default.createElement(Input, __assign({ ref: forwardedRef, type: "text", onChange: handleChange, readOnly: readOnly, disabled: readOnly, value: value, placeholder: placeholder }, rest)),
            react_1.default.createElement(ActionContainer, null,
                '' !== value && actions,
                readOnly && react_1.default.createElement(ReadOnlyIcon, { size: 16 })))));
});
exports.MediaLinkInput = MediaLinkInput;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6;
//# sourceMappingURL=MediaLinkInput.js.map
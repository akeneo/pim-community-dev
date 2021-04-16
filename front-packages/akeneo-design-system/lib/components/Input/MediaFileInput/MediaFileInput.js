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
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
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
exports.MediaFileInput = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var shared_1 = require("../../../shared");
var theme_1 = require("../../../theme");
var illustrations_1 = require("../../../illustrations");
var components_1 = require("../../../components");
var ProgressBar_1 = require("../../ProgressBar/ProgressBar");
var icons_1 = require("../../../icons");
var hooks_1 = require("../../../hooks");
var DefaultPicture_svg_1 = __importDefault(require("../../../../static/illustrations/DefaultPicture.svg"));
var MediaFileInputContainer = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  position: relative;\n  display: flex;\n  flex-direction: ", ";\n  align-items: center;\n  padding: 12px;\n  padding-top: ", "px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  height: ", "px;\n  gap: ", "px;\n  outline-style: none;\n  box-sizing: border-box;\n  background: ", ";\n  cursor: ", ";\n  overflow: hidden;\n\n  ", "\n"], ["\n  position: relative;\n  display: flex;\n  flex-direction: ", ";\n  align-items: center;\n  padding: 12px;\n  padding-top: ", "px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  height: ", "px;\n  gap: ", "px;\n  outline-style: none;\n  box-sizing: border-box;\n  background: ", ";\n  cursor: ", ";\n  overflow: hidden;\n\n  ",
    "\n"])), function (_a) {
    var isCompact = _a.isCompact;
    return (isCompact ? 'row' : 'column');
}, function (_a) {
    var isCompact = _a.isCompact;
    return (isCompact ? 12 : 20);
}, function (_a) {
    var invalid = _a.invalid;
    return (invalid ? theme_1.getColor('red', 100) : theme_1.getColor('grey', 80));
}, function (_a) {
    var isCompact = _a.isCompact;
    return (isCompact ? 74 : 180);
}, function (_a) {
    var isCompact = _a.isCompact;
    return (isCompact ? 10 : 0);
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? theme_1.getColor('grey', 20) : theme_1.getColor('white'));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? 'not-allowed' : 'auto');
}, function (_a) {
    var readOnly = _a.readOnly;
    return !readOnly && styled_components_1.css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      &:focus {\n        box-shadow: 0 0 0 2px ", ";\n      }\n      &:hover {\n        ", "\n      }\n    "], ["\n      &:focus {\n        box-shadow: 0 0 0 2px ", ";\n      }\n      &:hover {\n        ", "\n      }\n    "])), theme_1.getColor('blue', 40), illustrations_1.ImportIllustration.animatedMixin);
});
var Input = styled_components_1.default.input(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  position: absolute;\n  opacity: 0;\n  top: 0;\n  left: 0;\n  width: 100%;\n  height: 100%;\n  cursor: ", ";\n"], ["\n  position: absolute;\n  opacity: 0;\n  top: 0;\n  left: 0;\n  width: 100%;\n  height: 100%;\n  cursor: ", ";\n"])), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? 'not-allowed' : 'pointer');
});
var MediaFileLabel = styled_components_1.default.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  font-size: ", ";\n  font-weight: normal;\n  color: ", ";\n  flex-grow: 1;\n  display: flex;\n  align-items: flex-end;\n  text-overflow: ellipsis;\n  white-space: nowrap;\n  overflow: hidden;\n"], ["\n  font-size: ", ";\n  font-weight: normal;\n  color: ", ";\n  flex-grow: 1;\n  display: flex;\n  align-items: flex-end;\n  text-overflow: ellipsis;\n  white-space: nowrap;\n  overflow: hidden;\n"])), theme_1.getFontSize('default'), theme_1.getColor('grey', 140));
var MediaFilePlaceholder = styled_components_1.default(MediaFileLabel)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  color: ", ";\n"], ["\n  color: ", ";\n"])), theme_1.getColor('grey', 100));
var ReadOnlyIcon = styled_components_1.default(icons_1.LockIcon)(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  margin-left: 4px;\n"], ["\n  margin-left: 4px;\n"])));
var ActionContainer = styled_components_1.default.div(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  ", "\n\n  display: flex;\n  gap: 2px;\n  align-items: center;\n  color: ", ";\n"], ["\n  ",
    "\n\n  display: flex;\n  gap: 2px;\n  align-items: center;\n  color: ", ";\n"])), function (_a) {
    var isCompact = _a.isCompact;
    return !isCompact && styled_components_1.css(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n      position: absolute;\n      top: 8px;\n      right: 8px;\n    "], ["\n      position: absolute;\n      top: 8px;\n      right: 8px;\n    "])));
}, theme_1.getColor('grey', 100));
var UploadProgress = styled_components_1.default(ProgressBar_1.ProgressBar)(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  flex: 1;\n  width: 100%;\n"], ["\n  flex: 1;\n  width: 100%;\n"])));
var MediaFileImage = styled_components_1.default(components_1.Image)(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n  border: none;\n"], ["\n  border: none;\n"])));
var MediaFileInput = react_1.default.forwardRef(function (_a, forwardedRef) {
    var onChange = _a.onChange, value = _a.value, thumbnailUrl = _a.thumbnailUrl, uploadingLabel = _a.uploadingLabel, uploader = _a.uploader, _b = _a.size, size = _b === void 0 ? 'default' : _b, placeholder = _a.placeholder, clearTitle = _a.clearTitle, children = _a.children, uploadErrorLabel = _a.uploadErrorLabel, _c = _a.invalid, invalid = _c === void 0 ? false : _c, _d = _a.readOnly, readOnly = _d === void 0 ? false : _d, rest = __rest(_a, ["onChange", "value", "thumbnailUrl", "uploadingLabel", "uploader", "size", "placeholder", "clearTitle", "children", "uploadErrorLabel", "invalid", "readOnly"]);
    var containerRef = react_1.useRef(null);
    var internalInputRef = react_1.useRef(null);
    var isCompact = size === 'small';
    var _e = hooks_1.useBooleanState(false), isUploading = _e[0], startUploading = _e[1], stopUploading = _e[2];
    var _f = react_1.useState(thumbnailUrl), displayedThumbnailUrl = _f[0], setDisplayedThumbnailUrl = _f[1];
    var _g = hooks_1.useBooleanState(false), hasUploadFailed = _g[0], uploadFailed = _g[1], uploadSucceeded = _g[2];
    var _h = react_1.useState(0), progress = _h[0], setProgress = _h[1];
    forwardedRef = forwardedRef !== null && forwardedRef !== void 0 ? forwardedRef : internalInputRef;
    react_1.useEffect(function () {
        setDisplayedThumbnailUrl(thumbnailUrl);
    }, [thumbnailUrl]);
    var openFileExplorer = function () {
        if (forwardedRef && 'function' !== typeof forwardedRef && forwardedRef.current && !readOnly && onChange) {
            forwardedRef.current.click();
        }
    };
    var handleUpload = function (file) { return __awaiter(void 0, void 0, void 0, function () {
        var uploadedFile, error_1;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    startUploading();
                    _a.label = 1;
                case 1:
                    _a.trys.push([1, 3, 4, 5]);
                    return [4, uploader(file, setProgress)];
                case 2:
                    uploadedFile = _a.sent();
                    uploadSucceeded();
                    onChange === null || onChange === void 0 ? void 0 : onChange(uploadedFile);
                    return [3, 5];
                case 3:
                    error_1 = _a.sent();
                    uploadFailed();
                    console.error(error_1);
                    return [3, 5];
                case 4:
                    setProgress(0);
                    stopUploading();
                    return [7];
                case 5: return [2];
            }
        });
    }); };
    var handleChange = function (event) {
        event.preventDefault();
        event.stopPropagation();
        if (event.target.files)
            void handleUpload(event.target.files[0]);
    };
    var handleClear = function () { return !readOnly && (onChange === null || onChange === void 0 ? void 0 : onChange(null)); };
    hooks_1.useShortcut(shared_1.Key.Enter, openFileExplorer, containerRef);
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
    return (react_1.default.createElement(react_1.default.Fragment, null,
        react_1.default.createElement(MediaFileInputContainer, { ref: containerRef, tabIndex: readOnly ? -1 : 0, invalid: invalid || hasUploadFailed, readOnly: readOnly, isCompact: isCompact },
            !value && !isUploading && (react_1.default.createElement(Input, __assign({ ref: forwardedRef, type: "file", onChange: handleChange, readOnly: readOnly, disabled: readOnly, placeholder: placeholder }, rest))),
            isUploading ? (react_1.default.createElement(react_1.default.Fragment, null,
                react_1.default.createElement(MediaFileImage, { height: isCompact ? 47 : 120, width: isCompact ? 47 : 120, src: null, alt: uploadingLabel }),
                react_1.default.createElement(UploadProgress, { title: uploadingLabel, progressLabel: Math.round(progress * 100) + "%", level: "primary", percent: progress * 100 }))) : null !== value ? (react_1.default.createElement(react_1.default.Fragment, null,
                react_1.default.createElement(MediaFileImage, { height: isCompact ? 47 : 120, width: isCompact ? 47 : 120, src: displayedThumbnailUrl, alt: value.originalFilename, onError: function () { return setDisplayedThumbnailUrl(DefaultPicture_svg_1.default); } }),
                readOnly ? (react_1.default.createElement(MediaFilePlaceholder, null, value.originalFilename)) : (react_1.default.createElement(MediaFileLabel, null, value.originalFilename)))) : (react_1.default.createElement(react_1.default.Fragment, null,
                react_1.default.createElement(illustrations_1.ImportIllustration, { size: isCompact ? 47 : 180 }),
                react_1.default.createElement(MediaFilePlaceholder, null, hasUploadFailed ? uploadErrorLabel : placeholder))),
            react_1.default.createElement(ActionContainer, { isCompact: isCompact },
                value && (react_1.default.createElement(react_1.default.Fragment, null,
                    !readOnly && (react_1.default.createElement(components_1.IconButton, { size: "small", level: "tertiary", ghost: "borderless", icon: react_1.default.createElement(icons_1.CloseIcon, null), title: clearTitle, onClick: handleClear })),
                    actions)),
                readOnly && react_1.default.createElement(ReadOnlyIcon, { size: 16 })))));
});
exports.MediaFileInput = MediaFileInput;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10;
//# sourceMappingURL=MediaFileInput.js.map
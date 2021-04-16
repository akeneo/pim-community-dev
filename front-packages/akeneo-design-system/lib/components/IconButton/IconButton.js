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
exports.IconButton = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var Button_1 = require("../../components/Button/Button");
var IconButtonContainer = styled_components_1.default(Button_1.Button)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: inline-flex;\n  align-items: center;\n  justify-content: center;\n  padding: 0;\n  width: ", "px;\n  border-style: ", ";\n  ", ";\n"], ["\n  display: inline-flex;\n  align-items: center;\n  justify-content: center;\n  padding: 0;\n  width: ", "px;\n  border-style: ", ";\n  ",
    ";\n"])), function (_a) {
    var size = _a.size;
    return (size === 'small' ? 24 : 32);
}, function (_a) {
    var borderless = _a.borderless, ghost = _a.ghost;
    return (!borderless && ghost ? 'solid' : 'none');
}, function (_a) {
    var borderless = _a.borderless;
    return borderless && styled_components_1.css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      background: transparent;\n    "], ["\n      background: transparent;\n    "])));
});
var getIconSize = function (size) {
    switch (size) {
        case 'small':
            return 16;
        case 'default':
            return 20;
    }
};
var IconButton = react_1.default.forwardRef(function (_a, forwardedRef) {
    var icon = _a.icon, _b = _a.size, size = _b === void 0 ? 'default' : _b, ghost = _a.ghost, rest = __rest(_a, ["icon", "size", "ghost"]);
    return (react_1.default.createElement(IconButtonContainer, __assign({ ref: forwardedRef, ghost: true === ghost || 'borderless' === ghost, borderless: 'borderless' === ghost, size: size }, rest), react_1.default.cloneElement(icon, { size: getIconSize(size) })));
});
exports.IconButton = IconButton;
var templateObject_1, templateObject_2;
//# sourceMappingURL=IconButton.js.map
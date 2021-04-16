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
exports.Badge = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var theme_1 = require("../../theme");
var BadgeContainer = styled_components_1.default.span(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: inline-flex;\n  height: 18px;\n  line-height: 16px;\n  border: 1px solid;\n  padding: 0 6px;\n  border-radius: 2px;\n  text-transform: uppercase;\n  box-sizing: border-box;\n  background-color: ", ";\n  font-size: ", ";\n  font-weight: normal;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n  overflow: hidden;\n\n  ", "\n"], ["\n  display: inline-flex;\n  height: 18px;\n  line-height: 16px;\n  border: 1px solid;\n  padding: 0 6px;\n  border-radius: 2px;\n  text-transform: uppercase;\n  box-sizing: border-box;\n  background-color: ", ";\n  font-size: ", ";\n  font-weight: normal;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n  overflow: hidden;\n\n  ",
    "\n"])), theme_1.getColor('white'), theme_1.getFontSize('small'), function (_a) {
    var _b = _a.level, level = _b === void 0 ? 'primary' : _b;
    return styled_components_1.css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n    color: ", ";\n    border-color: ", ";\n  "], ["\n    color: ", ";\n    border-color: ", ";\n  "])), theme_1.getColorForLevel(level, 140), theme_1.getColorForLevel(level, 100));
});
var Badge = react_1.default.forwardRef(function (_a, forwardedRef) {
    var _b = _a.level, level = _b === void 0 ? 'primary' : _b, children = _a.children, rest = __rest(_a, ["level", "children"]);
    return (react_1.default.createElement(BadgeContainer, __assign({ level: level, ref: forwardedRef }, rest), children));
});
exports.Badge = Badge;
var templateObject_1, templateObject_2;
//# sourceMappingURL=Badge.js.map
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
exports.DownloadIcon = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var downloadAnimation = styled_components_1.keyframes(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  0%   {transform: translateY(0)}\n  25%  {transform: translateY(2px)}\n  50%  {transform: translateY(-2px)}\n  100% {transform: translateY(0)}\n"], ["\n  0%   {transform: translateY(0)}\n  25%  {transform: translateY(2px)}\n  50%  {transform: translateY(-2px)}\n  100% {transform: translateY(0)}\n"])));
var Arrow = styled_components_1.default.path(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  animation-duration: 0.5s;\n  animation-iteration-count: 1;\n"], ["\n  animation-duration: 0.5s;\n  animation-iteration-count: 1;\n"])));
var animatedMixin = styled_components_1.css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  ", " {\n    animation-name: ", ";\n  }\n"], ["\n  ", " {\n    animation-name: ", ";\n  }\n"])), Arrow, downloadAnimation);
var Container = styled_components_1.default.svg(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  :hover {\n    ", "\n  }\n"], ["\n  :hover {\n    ", "\n  }\n"])), function (_a) {
    var animateOnHover = _a.animateOnHover;
    return animateOnHover && animatedMixin;
});
var DownloadIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, _d = _a.animateOnHover, animateOnHover = _d === void 0 ? true : _d, props = __rest(_a, ["title", "size", "color", "animateOnHover"]);
    return (react_1.default.createElement(Container, __assign({ viewBox: "0 0 24 24", width: size, height: size, animateOnHover: animateOnHover }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("g", { stroke: color, fill: "none", fillRule: "evenodd", strokeLinecap: "round", strokeLinejoin: "round" },
            react_1.default.createElement("path", { d: "M17.11 17H20v5H4v-5h3" }),
            react_1.default.createElement(Arrow, { d: "M12 2v16V2zM17 13l-5 5.5L7 13h0" }))));
};
exports.DownloadIcon = DownloadIcon;
DownloadIcon.animatedMixin = animatedMixin;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=DownloadIcon.js.map
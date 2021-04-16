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
exports.ImportIllustration = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var Import_svg_1 = __importDefault(require("../../static/illustrations/Import.svg"));
var Stars = styled_components_1.default.g(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  fill: #5e63b6;\n  transform-origin: 50% 50%;\n  transition: transform 0.2s linear;\n"], ["\n  fill: #5e63b6;\n  transform-origin: 50% 50%;\n  transition: transform 0.2s linear;\n"])));
var Arrow = styled_components_1.default.g(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  fill: #9452ba;\n  transform-origin: 51% 32%;\n  transition: transform 0.3s ease-in-out;\n"], ["\n  fill: #9452ba;\n  transform-origin: 51% 32%;\n  transition: transform 0.3s ease-in-out;\n"])));
var animatedMixin = styled_components_1.css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  ", " {\n    transform: scale(1.2);\n  }\n  ", " {\n    transform: rotate(180deg);\n  }\n"], ["\n  ", " {\n    transform: scale(1.2);\n  }\n  ", " {\n    transform: rotate(180deg);\n  }\n"])), Stars, Arrow);
var Container = styled_components_1.default.svg(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  :hover {\n    ", "\n  }\n"], ["\n  :hover {\n    ", "\n  }\n"])), function (_a) {
    var animateOnHover = _a.animateOnHover;
    return animateOnHover && animatedMixin;
});
var ImportIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, _c = _a.animateOnHover, animateOnHover = _c === void 0 ? true : _c, props = __rest(_a, ["title", "size", "animateOnHover"]);
    return (react_1.default.createElement(Container, __assign({ width: size, height: size, viewBox: "0 0 256 256", animateOnHover: animateOnHover }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: Import_svg_1.default }),
        react_1.default.createElement(Stars, null,
            react_1.default.createElement("path", { d: "M218.1797,101.9522 C217.6267,101.9522 217.1797,101.5052 217.1797,100.9522 L217.1797,93.9522 C217.1797,93.3992 217.6267,92.9522 218.1797,92.9522 C218.7327,92.9522 219.1797,93.3992 219.1797,93.9522 L219.1797,100.9522 C219.1797,101.5052 218.7327,101.9522 218.1797,101.9522" }),
            react_1.default.createElement("path", { d: "M221.6797,98.4522 L214.6797,98.4522 C214.1267,98.4522 213.6797,98.0052 213.6797,97.4522 C213.6797,96.8992 214.1267,96.4522 214.6797,96.4522 L221.6797,96.4522 C222.2327,96.4522 222.6797,96.8992 222.6797,97.4522 C222.6797,98.0052 222.2327,98.4522 221.6797,98.4522" }),
            react_1.default.createElement("path", { d: "M212.251,159.4356 L208.313,159.4356 C208.037,159.4356 207.814,159.2116 207.814,158.9356 C207.814,158.6596 208.037,158.4356 208.313,158.4356 L212.251,158.4356 C212.527,158.4356 212.751,158.6596 212.751,158.9356 C212.751,159.2116 212.527,159.4356 212.251,159.4356" }),
            react_1.default.createElement("path", { d: "M210.2822,161.4043 C210.0062,161.4043 209.7822,161.1803 209.7822,160.9043 L209.7822,156.9673 C209.7822,156.6903 210.0062,156.4673 210.2822,156.4673 C210.5582,156.4673 210.7822,156.6903 210.7822,156.9673 L210.7822,160.9043 C210.7822,161.1803 210.5582,161.4043 210.2822,161.4043" }),
            react_1.default.createElement("path", { d: "M56.6792,48.4522 L49.6792,48.4522 C49.1272,48.4522 48.6792,48.0052 48.6792,47.4522 C48.6792,46.8992 49.1272,46.4522 49.6792,46.4522 L56.6792,46.4522 C57.2312,46.4522 57.6792,46.8992 57.6792,47.4522 C57.6792,48.0052 57.2312,48.4522 56.6792,48.4522" }),
            react_1.default.createElement("path", { d: "M53.1792,51.9522 C52.6272,51.9522 52.1792,51.5052 52.1792,50.9522 L52.1792,43.9522 C52.1792,43.3992 52.6272,42.9522 53.1792,42.9522 C53.7312,42.9522 54.1792,43.3992 54.1792,43.9522 L54.1792,50.9522 C54.1792,51.5052 53.7312,51.9522 53.1792,51.9522" }),
            react_1.default.createElement("path", { d: "M36.2822,117.4043 C36.0062,117.4043 35.7822,117.1803 35.7822,116.9043 L35.7822,112.9673 C35.7822,112.6903 36.0062,112.4673 36.2822,112.4673 C36.5582,112.4673 36.7822,112.6903 36.7822,112.9673 L36.7822,116.9043 C36.7822,117.1803 36.5582,117.4043 36.2822,117.4043" }),
            react_1.default.createElement("path", { d: "M38.251,115.4356 L34.313,115.4356 C34.037,115.4356 33.814,115.2116 33.814,114.9356 C33.814,114.6596 34.037,114.4356 34.313,114.4356 L38.251,114.4356 C38.527,114.4356 38.751,114.6596 38.751,114.9356 C38.751,115.2116 38.527,115.4356 38.251,115.4356" })),
        react_1.default.createElement(Arrow, null,
            react_1.default.createElement("path", { d: "M130.4976,90.1905 C129.6686,90.1905 128.9976,89.5185 128.9976,88.6905 L128.9976,74.9785 C128.9976,74.1505 129.6686,73.4785 130.4976,73.4785 C131.3266,73.4785 131.9976,74.1505 131.9976,74.9785 L131.9976,88.6905 C131.9976,89.5185 131.3266,90.1905 130.4976,90.1905" }),
            react_1.default.createElement("path", { d: "M130.4976,90.1905 C130.1136,90.1905 129.7296,90.0445 129.4366,89.7515 L124.5886,84.9035 C124.0026,84.3175 124.0026,83.3685 124.5886,82.7825 C125.1736,82.1965 126.1236,82.1965 126.7096,82.7825 L130.4976,86.5695 L134.2856,82.7825 C134.8716,82.1965 135.8206,82.1965 136.4066,82.7825 C136.9926,83.3685 136.9926,84.3175 136.4066,84.9035 L131.5586,89.7515 C131.2656,90.0445 130.8816,90.1905 130.4976,90.1905" }))));
};
exports.ImportIllustration = ImportIllustration;
ImportIllustration.animatedMixin = animatedMixin;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=ImportIllustration.js.map
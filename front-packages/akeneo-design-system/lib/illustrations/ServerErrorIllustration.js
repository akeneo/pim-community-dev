"use strict";
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
exports.ServerErrorIllustration = void 0;
var react_1 = __importDefault(require("react"));
var ServerError_svg_1 = __importDefault(require("../../static/illustrations/ServerError.svg"));
var theme_1 = require("../theme");
var ServerErrorIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 500 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 500 250" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: ServerError_svg_1.default }),
        react_1.default.createElement("g", { transform: "translate(1.000000, 8.000000)" },
            react_1.default.createElement(theme_1.BrandedPath, { d: "M264.15,146.35 C264.3,146.81 264.44,147.12 264.48,147.22 L264.48,147.22 C263.48,137.22 269.11,133.06 270.93,126.42 C270.94165,126.308644 270.961707,126.19833 270.99,126.09 C271.027883,125.938315 271.057923,125.784778 271.08,125.63 C271.35,123.79 270.6,121.63 269.17,119.43 L269.17,119.43 C269.05,119.24 268.93,119.06 268.8,118.87 L268.8,118.87 C268.57,118.53 268.32,118.19 268.05,117.87 L267.97,117.76 L267.75,117.48 L267.52,117.19 L267.29,116.91 L267.05,116.62 L266.87,116.42 C264.414066,113.677113 261.664239,111.212329 258.67,109.07 L258.67,109.07 C259.080103,112.566305 259.113628,116.096537 258.77,119.6 C258.200772,123.648452 257.010088,127.58479 255.24,131.27 C251.161549,130.966931 247.154344,130.035964 243.36,128.51 C240.141132,127.066937 237.089672,125.276299 234.26,123.17 C234.26,123.48 234.33,123.79 234.37,124.1 L234.37,124.38 C234.37,124.6 234.43,124.82 234.46,125.05 C234.49,125.28 234.46,125.25 234.46,125.36 C234.46,125.47 234.53,125.81 234.56,126.03 L234.56,126.3 C234.56,126.58 234.65,126.86 234.7,127.14 C234.698249,127.166638 234.698249,127.193362 234.7,127.22 C234.76,127.54 234.82,127.86 234.88,128.22 C234.878865,128.24332 234.878865,128.26668 234.88,128.29 C234.94,128.58 235,128.86 235.06,129.15 L235.06,129.32 L235.24,130.1 L235.24,130.25 C235.31,130.55 235.39,130.84 235.46,131.13 L235.46,131.13 C235.706667,132.07 235.98,132.973333 236.28,133.84 L236.28,133.84 C236.738562,135.218267 237.310518,136.556176 237.99,137.84 L238.27,138.34 L238.27,138.34 L238.27,138.34 L238.53,138.77 L238.53,138.77 L238.53,138.77 C238.63,138.92 238.72,139.07 238.82,139.21 L238.82,139.21 L238.82,139.27 L238.82,139.27 C238.95,139.46 239.08,139.63 239.21,139.81 L239.28,139.89 L239.28,139.89 C239.43,140.08 239.59,140.27 239.75,140.44 L240.09,140.79 L240.24,140.93 L240.46,141.13 L240.63,141.26 L240.84,141.43 L241.01,141.55 L241.23,141.7 L241.4,141.8 L241.63,141.93 L241.79,142.01 L242.06,142.13 L242.19,142.19 L242.6,142.33 L242.85,142.42 C249.52,144.18 256.02,141.42 264.17,147.22" }))));
};
exports.ServerErrorIllustration = ServerErrorIllustration;
//# sourceMappingURL=ServerErrorIllustration.js.map
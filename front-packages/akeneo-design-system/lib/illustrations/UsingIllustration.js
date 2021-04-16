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
exports.UsingIllustration = void 0;
var react_1 = __importDefault(require("react"));
var Using_svg_1 = __importDefault(require("../../static/illustrations/Using.svg"));
var theme_1 = require("../theme");
var UsingIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: Using_svg_1.default }),
        react_1.default.createElement("g", { transform: "translate(113.000000, 165.720000)" },
            react_1.default.createElement(theme_1.BrandedPath, { d: "M12.2,5.21 C11.9,6.94 11.17,8.94 10.221,10.73 C8.2,10.46 6.171,9.889 4.579,9.13 C3.26,8.47 1.76,7.48 0.34,6.33 L0.37,6.78 L0.39,6.92 L0.39,7.24 L0.41,7.38 L0.45,7.7 L0.45,7.84 C0.48,7.97 0.49,8.11 0.51,8.24 L0.58,8.76 C0.58,8.93 0.609,9.061 0.63,9.19 L0.65,9.29 L0.71,9.67 L0.73,9.74 L0.81,10.17 C0.91,10.62 1.01,11.07 1.131,11.47 C1.329,12.2 1.58,12.87 1.859,13.43 L1.98,13.68 C2.03,13.76 2.05,13.84 2.1,13.91 C2.14,14.01 2.18,14.07 2.22,14.14 L2.42,14.44 L2.45,14.5 C2.52,14.6 2.59,14.7 2.67,14.78 L2.81,14.96 L2.88,15.03 C2.921,15.06 2.98,15.09 2.98,15.13 L3.08,15.21 C3.1,15.24 3.12,15.27 3.18,15.31 C3.2,15.31 3.22,15.34 3.24,15.36 L3.34,15.43 C3.37,15.45 3.4,15.46 3.409,15.49 C3.44,15.53 3.48,15.549 3.53,15.59 C3.56,15.59 3.579,15.61 3.6,15.62 L3.73,15.68 C3.73,15.68 3.77,15.68 3.791,15.7 L3.99,15.78 C4.029,15.78 4.07,15.811 4.091,15.82 C7.26,16.82 10.52,15.66 14.29,18.65 C14.04,13.831 16.86,11.97 17.89,8.82 L17.9,8.82 L17.95,8.66 C17.95,8.59 17.989,8.52 17.989,8.46 C18.159,7.58 17.86,6.52 17.22,5.42 L17.04,5.119 L16.69,4.62 C16.69,4.59 16.671,4.58 16.65,4.55 L16.55,4.42 L16.449,4.27 C16.421,4.21 16.38,4.17 16.35,4.13 L16.25,3.98 C16.21,3.96 16.18,3.91 16.15,3.88 C15.15,2.581 13.81,1.3 12.4,0.16 C12.5,1.96 12.45,3.79 12.2,5.21 Z" }))));
};
exports.UsingIllustration = UsingIllustration;
//# sourceMappingURL=UsingIllustration.js.map
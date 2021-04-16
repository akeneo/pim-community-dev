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
exports.ClientErrorIllustration = void 0;
var react_1 = __importDefault(require("react"));
var ClientError_svg_1 = __importDefault(require("../../static/illustrations/ClientError.svg"));
var theme_1 = require("../theme");
var ClientErrorIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 500 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 500 250" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: ClientError_svg_1.default }),
        react_1.default.createElement("g", { transform: "translate(1.000000, 8.000000)" },
            react_1.default.createElement(theme_1.BrandedPath, { d: "M252.39,136 C252.45,136.18 252.5,136.3 252.52,136.34 L252.52,136.34 C252.12,132.45 254.33,130.82 255.04,128.24 C255.036758,128.196727 255.036758,128.153273 255.04,128.11 C255.043053,128.050039 255.043053,127.989961 255.04,127.93 C255.085434,127.061431 254.823434,126.204618 254.3,125.51 L254.3,125.51 L254.16,125.29 L254.16,125.29 L253.87,124.89 L253.87,124.89 L253.78,124.78 L253.69,124.67 L253.6,124.56 L253.51,124.45 L253.44,124.37 C252.484426,123.302908 251.414697,122.34384 250.25,121.51 L250.25,121.51 C250.396646,122.872733 250.396646,124.247267 250.25,125.61 C250.030222,127.188136 249.568112,128.722879 248.88,130.16 C247.290794,130.043756 245.729064,129.682838 244.25,129.09 C242.994353,128.528205 241.803938,127.830722 240.7,127.01 L240.7,127.37 L240.7,127.48 C240.7,127.57 240.7,127.65 240.7,127.74 L240.7,127.86 L240.7,128.12 L240.7,128.23 L240.76,128.56 L240.76,128.56 L240.83,128.94 L240.83,128.94 L240.9,129.27 L240.9,129.34 L240.97,129.64 L240.97,129.7 L241.06,130.04 L241.06,130.04 C241.16,130.406667 241.266667,130.76 241.38,131.1 L241.38,131.1 C241.557606,131.630484 241.778343,132.145538 242.04,132.64 L242.15,132.83 L242.15,132.83 L242.25,133 L242.25,133 L242.36,133.17 L242.36,133.17 L242.36,133.17 L242.36,133.17 L242.51,133.38 L242.51,133.38 L242.51,133.38 L242.69,133.59 L242.82,133.73 L242.88,133.78 L242.96,133.86 L243.02,133.91 L243.1,133.97 L243.17,133.97 L243.25,134.03 L243.32,134.03 L243.41,134.08 L243.47,134.08 L243.58,134.08 L243.63,134.08 L243.79,134.13 L243.89,134.13 C246.49,134.82 249.02,133.73 252.2,136" }))));
};
exports.ClientErrorIllustration = ClientErrorIllustration;
//# sourceMappingURL=ClientErrorIllustration.js.map
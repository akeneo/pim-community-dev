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
exports.ProductsIllustration = void 0;
var react_1 = __importDefault(require("react"));
var Products_svg_1 = __importDefault(require("../../static/illustrations/Products.svg"));
var theme_1 = require("../theme");
var ProductsIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: Products_svg_1.default }),
        react_1.default.createElement("g", { fill: "none", fillRule: "evenodd" },
            react_1.default.createElement(theme_1.BrandedPath, { d: "M131.266 96.007c.104.308.195.518.223.588.008.007-.009-.004 0 0 0 0 0 .002 0 0 .001.001 0 0 0 0-.7-6.74 3.123-9.549 4.351-14.026.006-.067.02-.139.04-.22.025-.1.045-.204.061-.308.182-1.238-.325-2.68-1.286-4.18-.004-.01-.01-.018-.016-.025-.078-.126-.163-.25-.248-.375 0 0-.003-.002-.004-.005a16.864 16.864 0 00-.503-.689c-.018-.024-.035-.049-.054-.072l-.149-.188-.153-.194-.158-.189-.165-.195-.118-.136c-1.51-1.74-3.476-3.459-5.528-4.954 0 0-.015-.023 0 0 .272 2.536.306 5.073.066 7.101-.29 2.445-1.165 5.292-2.379 7.872-2.842-.232-5.747-.89-8.012-1.858-1.893-.808-4.063-2.085-6.137-3.598.023.211.048.42.072.63l.025.187c.02.15.038.298.061.45l.03.206a21.043 21.043 0 00.098.631 25.593 25.593 0 00.107.62c.038.22.08.435.123.651.002.016.007.031.01.05l.121.576c.01.04.018.078.025.117l.121.523c.01.033.018.066.025.102l.15.596a28.163 28.163 0 00.553 1.833l.007.021c.337.98.721 1.881 1.15 2.67.063.114.127.227.192.337.003.004.003.01.006.013h.001c.06.1.12.2.178.293l.002.002.007.011c.064.103.13.204.197.3l.03.04s-.002.001 0 .002a9.936 9.936 0 00.308.417c.005.007.011.013.016.022.102.13.207.254.316.371.074.083.151.16.228.237.032.033.067.062.1.093.047.046.096.088.144.132.037.032.074.06.112.091.046.04.094.075.141.113l.114.081.146.101.114.07.157.09.11.055c.06.028.12.054.181.081.028.01.06.026.09.037.093.038.186.07.28.095.063.02.116.038.167.058 4.5 1.188 8.882-.695 14.377 3.234" }))));
};
exports.ProductsIllustration = ProductsIllustration;
//# sourceMappingURL=ProductsIllustration.js.map
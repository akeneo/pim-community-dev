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
exports.FamilyVariantIllustration = void 0;
var react_1 = __importDefault(require("react"));
var FamilyVariant_svg_1 = __importDefault(require("../../static/illustrations/FamilyVariant.svg"));
var theme_1 = require("../theme");
var FamilyVariantIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: FamilyVariant_svg_1.default }),
        react_1.default.createElement(theme_1.BrandedPath, { d: "M100.267 107.098c.104.31.195.52.223.588.008.008-.01-.004 0 0 0 0 0 .002 0 0 0 .002-.002 0-.002 0-.698-6.74 3.124-9.549 4.353-14.026.006-.067.019-.139.039-.22.026-.1.046-.204.062-.308.182-1.238-.325-2.68-1.287-4.18-.004-.01-.01-.018-.015-.026-.078-.125-.163-.25-.248-.374l-.004-.005a17.584 17.584 0 00-.503-.689c-.018-.024-.035-.049-.054-.072l-.15-.188-.153-.194-.157-.189-.165-.195c-.037-.044-.078-.09-.12-.136-1.508-1.739-3.474-3.459-5.526-4.954 0 0-.016-.023 0 0 .27 2.536.306 5.073.065 7.102-.29 2.445-1.165 5.292-2.38 7.872-2.84-.233-5.745-.892-8.01-1.859-1.894-.808-4.063-2.085-6.137-3.598.023.211.048.42.072.63l.025.187c.019.15.038.3.06.45l.03.206c.023.151.045.3.07.45l.029.181c.03.19.063.377.098.566.002.018.004.037.009.054.038.22.08.435.123.651.002.016.007.032.01.05.039.194.079.384.12.576l.026.117c.039.175.08.35.12.523.01.033.019.067.026.102.049.2.098.4.15.596.002.001.002.003.002.003.168.633.352 1.244.55 1.83.003.008.006.014.007.021.338.98.722 1.881 1.15 2.67.064.114.128.227.192.337.004.004.004.01.007.014.06.1.119.2.179.292l.002.002.007.011c.064.103.129.203.197.3l.028.038v.004c.087.125.177.246.265.361l.045.056.016.022c.102.13.207.254.316.371.074.083.15.161.227.237.033.033.068.062.1.093.048.046.097.088.145.132.037.032.074.06.112.091.046.04.093.075.14.113.038.028.076.053.115.081.048.035.096.068.146.101l.114.07c.052.031.106.06.157.088.037.021.074.04.109.057.06.028.119.055.182.081.028.011.059.026.09.037.092.038.186.07.279.095.063.02.117.038.168.058 4.5 1.19 8.882-.695 14.377 3.234" })));
};
exports.FamilyVariantIllustration = FamilyVariantIllustration;
//# sourceMappingURL=FamilyVariantIllustration.js.map
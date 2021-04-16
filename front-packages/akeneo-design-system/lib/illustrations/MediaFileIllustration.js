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
exports.MediaFileIllustration = void 0;
var react_1 = __importDefault(require("react"));
var MediaFile_svg_1 = __importDefault(require("../../static/illustrations/MediaFile.svg"));
var theme_1 = require("../theme");
var MediaFileIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: MediaFile_svg_1.default }),
        react_1.default.createElement("g", { fill: "none", fillRule: "evenodd" },
            react_1.default.createElement(theme_1.BrandedPath, { d: "M132.266 107.128c.104.308.196.52.223.588.008.007-.008-.004 0 0 0 0 0 .002 0 0h-.001c-.7-6.74 3.124-9.549 4.352-14.026.007-.067.02-.139.039-.22.026-.1.047-.204.062-.308.183-1.238-.325-2.68-1.286-4.18-.004-.01-.011-.018-.015-.026-.08-.125-.164-.25-.25-.375l-.002-.004a17.162 17.162 0 00-.504-.689c-.017-.024-.034-.049-.054-.072l-.15-.188-.152-.194a28.276 28.276 0 00-.322-.384l-.12-.136c-1.508-1.74-3.475-3.459-5.526-4.954 0 0-.016-.023 0 0 .27 2.536.306 5.073.065 7.102-.29 2.445-1.165 5.291-2.38 7.872-2.84-.233-5.746-.892-8.01-1.859-1.894-.808-4.064-2.085-6.138-3.598.023.211.048.42.072.63l.026.187c.019.15.037.3.06.45.009.068.019.137.03.205.022.152.044.301.069.45l.029.182a33.24 33.24 0 00.107.62c.039.22.08.435.124.651l.009.05c.04.194.08.384.122.576l.025.117a49.235 49.235 0 00.145.625c.05.2.099.4.15.596.002.001.002.003.002.003.168.633.352 1.244.552 1.83 0 .007.004.014.006.021.337.98.72 1.881 1.15 2.67.063.114.128.227.192.337.003.004.003.01.006.013l.002.001c.058.1.118.198.177.292l.002.002.008.01c.063.104.129.204.197.3v.001a.337.337 0 00.028.038v.004a11.294 11.294 0 00.31.417l.015.021c.103.13.207.255.317.372.074.083.15.161.227.237.032.032.067.062.099.093.049.046.097.088.145.132.037.032.074.06.113.091.045.038.093.075.14.113.037.028.075.053.114.081.049.035.097.067.146.1.037.024.076.048.114.07.053.032.106.06.158.09.036.02.073.038.108.056.06.028.12.054.182.081.028.01.06.026.09.037.093.038.187.07.28.095.063.018.116.038.168.058 4.5 1.19 8.88-.695 14.376 3.234" }))));
};
exports.MediaFileIllustration = MediaFileIllustration;
//# sourceMappingURL=MediaFileIllustration.js.map
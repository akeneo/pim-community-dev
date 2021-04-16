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
exports.ViewsIllustration = void 0;
var react_1 = __importDefault(require("react"));
var Views_svg_1 = __importDefault(require("../../static/illustrations/Views.svg"));
var theme_1 = require("../theme");
var ViewsIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: Views_svg_1.default }),
        react_1.default.createElement("g", { fill: "none", fillRule: "evenodd" },
            react_1.default.createElement(theme_1.BrandedPath, { d: "M131.266 96.007c.104.309.195.519.223.588.008.007-.009-.004 0 0 0 0 0 .002 0 0 .001 0 0 0 0 0-.7-6.74 3.123-9.55 4.351-14.026.006-.066.02-.14.04-.221.025-.1.045-.203.061-.306.182-1.24-.325-2.681-1.286-4.182-.004-.01-.01-.017-.016-.024-.078-.125-.163-.25-.248-.375 0 0-.003-.002-.004-.005a16.864 16.864 0 00-.503-.69c-.018-.023-.035-.048-.054-.071l-.149-.188c-.052-.066-.102-.131-.153-.194l-.158-.188-.165-.196-.118-.136c-1.51-1.74-3.476-3.458-5.528-4.954 0 0-.015-.023 0 0 .272 2.536.306 5.073.066 7.102-.29 2.445-1.165 5.292-2.379 7.872-2.842-.233-5.747-.891-8.012-1.858-1.893-.81-4.063-2.085-6.137-3.6.023.212.048.42.072.63l.025.188c.02.15.038.299.061.449l.03.207a21.043 21.043 0 00.098.632 25.503 25.503 0 00.107.62c.038.218.08.434.123.65.002.017.007.032.01.049l.121.577c.01.04.018.078.025.117l.121.523c.01.033.018.067.025.102l.15.596a28.163 28.163 0 00.553 1.833l.007.02c.337.98.721 1.882 1.15 2.67.063.116.127.228.192.338.003.004.003.009.006.013h.001l.178.293.002.002.007.01c.064.104.13.205.197.3.01.015.018.026.03.04 0 0-.002.002 0 .003a9.936 9.936 0 00.308.417c.005.007.011.013.016.022a6.04 6.04 0 00.544.608c.032.033.067.062.1.093.047.046.096.088.144.132.037.032.074.06.112.09.046.04.094.076.141.114.037.028.075.054.114.08l.146.102.114.07.157.089a3.723 3.723 0 00.291.137c.028.01.06.027.09.038.093.037.186.068.28.094.063.019.116.038.167.058 4.5 1.189 8.882-.695 14.377 3.234" }))));
};
exports.ViewsIllustration = ViewsIllustration;
//# sourceMappingURL=ViewsIllustration.js.map
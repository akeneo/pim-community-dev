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
exports.FamilyIllustration = void 0;
var react_1 = __importDefault(require("react"));
var Family_svg_1 = __importDefault(require("../../static/illustrations/Family.svg"));
var theme_1 = require("../theme");
var FamilyIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: Family_svg_1.default }),
        react_1.default.createElement(theme_1.BrandedPath, { d: "M131.266 103.844c.104.308.195.52.223.588.008.007-.009-.004 0 0 0 0 0 .002 0 0 .001.001 0 0 0 0-.7-6.74 3.123-9.549 4.351-14.026.006-.067.02-.139.04-.22.025-.1.045-.204.061-.308.182-1.238-.325-2.68-1.286-4.18-.004-.01-.01-.018-.016-.026-.078-.125-.163-.25-.248-.375l-.004-.004a16.2 16.2 0 00-.503-.689c-.018-.024-.035-.049-.054-.072l-.149-.188-.153-.194a22.65 22.65 0 01-.158-.189l-.165-.195-.118-.136c-1.51-1.74-3.476-3.459-5.528-4.954 0 0-.015-.023 0 0 .272 2.536.306 5.073.066 7.102-.29 2.445-1.165 5.291-2.379 7.872-2.842-.233-5.747-.892-8.012-1.859-1.893-.808-4.063-2.085-6.137-3.598.023.211.048.42.072.63l.025.187c.02.15.038.3.061.45l.03.205a20.201 20.201 0 00.098.632 25.593 25.593 0 00.107.62c.038.22.08.435.123.651.002.016.007.032.01.05l.121.576c.01.04.018.078.025.117l.121.523c.01.033.018.067.025.102l.15.596c.002.001.002.003.002.003.168.633.352 1.244.551 1.83l.007.021c.337.98.721 1.881 1.15 2.67.063.114.127.227.192.337.003.004.003.01.006.013l.001.001.178.292.002.002.007.01c.064.104.13.204.197.3v.001c.01.014.018.025.03.038 0 .001-.002.003 0 .004a9.936 9.936 0 00.308.417l.016.021a6.062 6.062 0 00.544.61c.032.031.067.061.1.092.047.046.096.088.144.132.037.032.074.06.112.091l.141.113c.037.028.075.053.114.081l.146.1c.037.024.076.048.114.07.053.032.106.06.157.09.037.02.073.038.11.056.06.028.12.054.181.081.028.01.06.026.09.037.093.038.186.07.28.095.063.018.116.038.167.058 4.5 1.19 8.882-.695 14.377 3.234" })));
};
exports.FamilyIllustration = FamilyIllustration;
//# sourceMappingURL=FamilyIllustration.js.map
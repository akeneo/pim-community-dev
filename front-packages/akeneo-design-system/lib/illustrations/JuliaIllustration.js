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
exports.JuliaIllustration = void 0;
var react_1 = __importDefault(require("react"));
var Julia_svg_1 = __importDefault(require("../../static/illustrations/Julia.svg"));
var theme_1 = require("../theme");
var JuliaIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: Julia_svg_1.default }),
        react_1.default.createElement("g", { transform: "translate(119, 169.5)" },
            react_1.default.createElement(theme_1.BrandedPath, { d: "M12.202,5.434 C11.909,7.163 11.174,9.16 10.223,10.949 C8.201,10.681 6.169,10.107 4.579,9.336 C3.262,8.687 1.763,7.699 0.339,6.553 C0.351,6.699 0.363,6.859 0.375,7.005 C0.374,7.049 0.387,7.093 0.387,7.136 C0.4,7.239 0.397,7.355 0.411,7.457 C0.41,7.501 0.423,7.559 0.423,7.603 C0.435,7.705 0.448,7.822 0.46,7.923 C0.46,7.968 0.474,8.012 0.473,8.055 C0.486,8.186 0.497,8.333 0.524,8.464 C0.55,8.669 0.562,8.814 0.588,8.975 C0.614,9.15 0.627,9.282 0.654,9.413 C0.653,9.443 0.667,9.471 0.666,9.501 C0.693,9.633 0.706,9.749 0.733,9.881 C0.733,9.91 0.747,9.925 0.747,9.954 C0.773,10.1 0.8,10.246 0.827,10.378 C0.92,10.831 1.029,11.27 1.153,11.695 C1.359,12.412 1.596,13.072 1.878,13.644 C1.92,13.732 1.962,13.806 2.005,13.894 C2.047,13.982 2.075,14.056 2.117,14.129 C2.16,14.217 2.202,14.291 2.244,14.364 C2.315,14.482 2.372,14.57 2.429,14.659 C2.457,14.703 2.457,14.718 2.472,14.718 C2.543,14.807 2.614,14.909 2.686,14.998 C2.743,15.058 2.786,15.116 2.843,15.176 C2.871,15.205 2.886,15.221 2.915,15.25 C2.943,15.28 2.987,15.31 3.014,15.354 C3.043,15.384 3.058,15.398 3.087,15.428 C3.115,15.457 3.144,15.487 3.187,15.517 C3.216,15.532 3.244,15.562 3.259,15.577 C3.287,15.606 3.33,15.637 3.359,15.651 C3.389,15.666 3.418,15.682 3.431,15.71 C3.46,15.74 3.503,15.755 3.547,15.786 C3.576,15.8 3.605,15.816 3.619,15.831 C3.663,15.845 3.706,15.876 3.749,15.891 C3.764,15.906 3.793,15.906 3.807,15.921 C3.865,15.951 3.937,15.981 3.995,15.997 C4.039,16.013 4.082,16.028 4.111,16.043 C7.273,17.045 10.532,15.879 14.3,18.872 C14.049,14.046 16.868,12.187 17.915,9.045 C17.915,9.001 17.931,8.943 17.946,8.885 C17.962,8.813 17.993,8.74 17.994,8.667 C18.17,7.797 17.867,6.742 17.23,5.638 C17.159,5.535 17.102,5.447 17.046,5.344 C16.947,5.182 16.833,5.006 16.704,4.828 C16.69,4.813 16.676,4.799 16.662,4.769 C16.633,4.725 16.59,4.681 16.562,4.637 C16.533,4.593 16.491,4.533 16.462,4.489 C16.434,4.445 16.391,4.4 16.363,4.356 C16.32,4.312 16.291,4.253 16.249,4.208 C16.22,4.179 16.191,4.134 16.163,4.105 C15.15,2.806 13.816,1.516 12.406,0.383 C12.507,2.191 12.446,4.011 12.202,5.434 Z" }))));
};
exports.JuliaIllustration = JuliaIllustration;
//# sourceMappingURL=JuliaIllustration.js.map
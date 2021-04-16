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
exports.CityIllustration = void 0;
var react_1 = __importDefault(require("react"));
var City_svg_1 = __importDefault(require("../../static/illustrations/City.svg"));
var theme_1 = require("../theme");
var CityIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: City_svg_1.default }),
        react_1.default.createElement(theme_1.BrandedPath, { d: "M91.888 64.625a.496.496 0 01-.031-.103c-.014-.047-.018-.099-.04-.142-.168-.563-.672-1.085-1.387-1.531a2.18 2.18 0 01-.199-.115 5.33 5.33 0 00-.403-.217c-.03-.017-.069-.03-.099-.046-.03-.017-.074-.038-.104-.055s-.07-.03-.1-.047c-.038-.013-.073-.038-.112-.05-.026-.01-.056-.026-.082-.035-.995-.444-2.175-.783-3.35-1.007.62 1.025 1.15 2.107 1.45 3.014.365 1.1.556 2.494.557 3.835-1.265.471-2.63.766-3.798.81-.97.03-2.153-.081-3.34-.309l.161.253c.013.026.035.047.048.073.04.056.074.125.113.18.014.026.04.056.053.082l.122.175c.013.026.035.048.048.073.048.073.1.155.157.223.078.112.13.193.196.279.07.094.117.167.174.235.009.017.026.03.035.047.056.069.1.133.156.202.009.017.022.02.03.038.062.077.123.154.18.223.195.235.395.458.599.667.343.355.686.667 1.029.914.052.038.1.068.151.106.052.039.091.073.139.103.052.038.1.068.147.098.078.046.139.08.2.114.03.017.034.026.043.022.069.03.142.067.212.097.052.017.095.038.147.055l.065.02c.025.01.06.013.09.03l.065.021a.42.42 0 00.087.021.21.21 0 01.06.013c.026.008.06.012.082.012.022 0 .043 0 .06.012.026.009.057.004.091.008a.21.21 0 01.056.004c.03-.005.065 0 .095-.005.013.004.03-.005.043 0a.67.67 0 00.134-.014c.03-.005.06-.01.082-.01 2.157-.397 3.698-2.09 6.829-1.512-1.646-2.74-.577-4.7-.941-6.86z" })));
};
exports.CityIllustration = CityIllustration;
//# sourceMappingURL=CityIllustration.js.map
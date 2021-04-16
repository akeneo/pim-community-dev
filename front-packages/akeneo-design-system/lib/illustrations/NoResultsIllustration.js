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
exports.NoResultsIllustration = void 0;
var react_1 = __importDefault(require("react"));
var NoResults_svg_1 = __importDefault(require("../../static/illustrations/NoResults.svg"));
var theme_1 = require("../theme");
var NoResultsIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: NoResults_svg_1.default }),
        react_1.default.createElement("g", { fill: "none", fillRule: "evenodd" },
            react_1.default.createElement(theme_1.BrandedPath, { d: "M129.47 84.27c-.431 2.548-1.516 5.493-2.918 8.132-2.984-.396-5.982-1.243-8.329-2.379-1.941-.958-4.155-2.414-6.254-4.104l.052.666c-.001.064.019.129.018.194.019.15.016.322.035.472-.001.065.018.152.017.216l.057.474c-.001.064.02.129.018.193.018.194.036.409.075.602.038.302.056.517.094.754.04.259.059.452.097.646-.001.043.021.086.02.13.039.193.058.366.097.559-.001.044.021.065.02.108.039.215.079.431.118.626.139.668.3 1.315.482 1.941.305 1.058.654 2.031 1.07 2.875.062.129.125.238.188.369.061.129.102.237.165.345.061.131.125.24.187.348a26 26 0 00.272.434c.043.065.043.087.064.087.105.13.21.283.315.414.085.087.148.174.232.262l.107.109c.041.044.106.088.147.153l.106.109a.863.863 0 00.149.131.486.486 0 01.105.088.639.639 0 00.15.11c.041.022.084.044.105.088.043.043.106.066.171.11.041.022.084.045.106.066.064.023.128.067.192.09.021.021.064.022.085.045.086.044.192.089.277.111.065.023.13.045.171.068 4.667 1.477 9.473-.242 15.035 4.171-.371-7.115 3.787-9.857 5.331-14.491 0-.064.024-.149.048-.235.024-.107.068-.213.069-.321.26-1.283-.187-2.838-1.127-4.466a4.537 4.537 0 01-.271-.433c-.147-.239-.315-.5-.504-.761a.31.31 0 01-.063-.087c-.042-.065-.105-.131-.147-.196-.042-.065-.105-.153-.147-.217-.042-.065-.105-.131-.147-.196-.064-.065-.105-.153-.168-.217-.043-.045-.085-.109-.127-.153-1.494-1.917-3.463-3.819-5.543-5.489.147 2.667.057 5.35-.302 7.449z" }))));
};
exports.NoResultsIllustration = NoResultsIllustration;
//# sourceMappingURL=NoResultsIllustration.js.map
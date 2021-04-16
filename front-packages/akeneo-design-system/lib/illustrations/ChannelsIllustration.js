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
exports.ChannelsIllustration = void 0;
var react_1 = __importDefault(require("react"));
var Channels_svg_1 = __importDefault(require("../../static/illustrations/Channels.svg"));
var theme_1 = require("../theme");
var ChannelsIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: Channels_svg_1.default }),
        react_1.default.createElement(theme_1.BrandedPath, { d: "M224.809 147.169c.065.194.123.326.14.37.006.005-.005-.002 0 0 0 0 0 .002 0 0-.441-4.25 1.969-6.021 2.745-8.845a.903.903 0 01.024-.14c.016-.062.029-.127.039-.193.115-.781-.205-1.69-.811-2.636l-.01-.016c-.05-.08-.103-.157-.156-.236 0 0-.002-.001-.002-.003-.1-.144-.206-.29-.318-.435l-.034-.045-.095-.12-.096-.121-.1-.12-.103-.122-.075-.086c-.952-1.097-2.192-2.181-3.486-3.124 0 0-.01-.015 0 0 .17 1.6.193 3.199.042 4.478-.183 1.542-.735 3.337-1.501 4.964-1.792-.146-3.623-.561-5.052-1.171-1.194-.51-2.562-1.315-3.871-2.27l.046.397.016.119c.012.094.024.188.039.283.005.044.012.087.018.13l.043.284.019.115c.019.119.04.237.06.355l.007.036c.025.137.05.274.078.41 0 .01.004.02.007.03.025.124.049.244.075.365l.017.074.075.33c.007.02.012.042.017.064.03.126.06.252.095.376v.002c.106.398.222.784.348 1.153 0 .005.003.009.004.014.213.617.455 1.186.726 1.684l.12.212c.003.002.003.005.005.008.037.064.075.126.112.186l.006.007c.04.065.082.129.125.189.006.009.01.017.017.025v.002c.055.079.112.155.166.228a.467.467 0 01.03.035c.003.004.007.008.01.014.064.08.13.16.199.233.047.053.094.102.143.15l.063.059c.03.029.06.056.092.083.022.02.046.037.07.058.028.024.059.047.089.07.023.019.047.034.072.052l.092.063.072.044c.033.02.067.037.099.057.023.012.047.023.068.035.038.018.076.034.115.05.018.008.037.018.057.024.059.025.117.044.176.06.04.013.074.024.106.037 2.838.75 5.6-.438 9.067 2.039" })));
};
exports.ChannelsIllustration = ChannelsIllustration;
//# sourceMappingURL=ChannelsIllustration.js.map
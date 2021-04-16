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
exports.EntitiesRecordIllustration = void 0;
var react_1 = __importDefault(require("react"));
var EntitiesRecord_svg_1 = __importDefault(require("../../static/illustrations/EntitiesRecord.svg"));
var theme_1 = require("../theme");
var EntitiesRecordIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: EntitiesRecord_svg_1.default }),
        react_1.default.createElement(theme_1.BrandedPath, { d: "M138.534 112.8c.084.251.159.422.181.478.007.006-.007 0 0 0-.568-5.482 2.54-7.766 3.54-11.407.006-.06.017-.12.032-.179.02-.082.037-.166.05-.25a5.217 5.217 0 00-1.059-3.419c-.064-.1-.133-.2-.2-.305a15.518 15.518 0 00-.409-.561c-.015-.019-.028-.039-.044-.058-.04-.051-.082-.1-.121-.153l-.125-.158-.129-.153a10.168 10.168 0 00-.133-.159 27.6 27.6 0 00-4.6-4.139c.225 1.918.243 3.854.054 5.775a21.793 21.793 0 01-1.935 6.4 21.793 21.793 0 01-6.514-1.512 26.491 26.491 0 01-4.991-2.927c.018.172.039.341.058.512l.021.153c.016.122.03.243.049.365l.024.168.056.366.024.148c.026.154.051.306.08.459a21.53 21.53 0 00.107.574c0 .014.006.026.009.04.032.158.064.313.1.469.007.033.014.064.02.1a21.76 21.76 0 00.121.508 23.41 23.41 0 00.571 1.973l.005.018c.252.748.565 1.474.935 2.171.052.093.1.185.157.274v.011c.047.081.1.162.144.238v.008c.053.084.106.166.161.244l.023.032c.07.1.143.2.214.294l.037.046.013.018c.083.1.168.206.257.3.06.067.122.131.185.193.026.027.054.05.08.076.026.026.079.072.119.107.04.035.059.049.091.074l.114.091c.038.03.061.045.093.066.032.021.079.056.119.083l.093.057c.042.025.086.048.127.072l.089.046.147.066c.023.008.049.021.074.03.074.03.15.056.227.077.052.015.095.031.137.047 3.659.967 7.223-.565 11.692 2.63" })));
};
exports.EntitiesRecordIllustration = EntitiesRecordIllustration;
//# sourceMappingURL=EntitiesRecordIllustration.js.map
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
exports.LocaleIllustration = void 0;
var react_1 = __importDefault(require("react"));
var Locale_svg_1 = __importDefault(require("../../static/illustrations/Locale.svg"));
var theme_1 = require("../theme");
var LocaleIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: Locale_svg_1.default }),
        react_1.default.createElement(theme_1.BrandedPath, { d: "M137.565 113.163c.078.23.145.386.166.438.007.005-.006-.002 0 0 0 0 0 .002 0 0h-.001c-.52-5.013 2.324-7.102 3.238-10.433a.925.925 0 01.03-.164c.018-.075.034-.151.044-.229.137-.922-.242-1.994-.957-3.11-.002-.007-.008-.013-.01-.019a10.3 10.3 0 00-.185-.278s-.003-.001-.003-.003a14.074 14.074 0 00-.375-.513c-.012-.017-.025-.037-.04-.053l-.11-.14c-.038-.05-.076-.099-.114-.145-.04-.046-.08-.093-.118-.14l-.123-.145c-.026-.032-.058-.067-.088-.1-1.124-1.294-2.585-2.574-4.112-3.687 0 0-.011-.015 0 0 .203 1.888.229 3.775.05 5.284-.216 1.819-.867 3.936-1.77 5.855-2.113-.174-4.274-.663-5.96-1.383-1.408-.6-3.021-1.55-4.564-2.676.016.156.035.311.053.468l.019.14c.014.112.028.222.045.334.006.052.014.102.022.154l.052.334.021.136.072.42.008.04a24.365 24.365 0 00.207 1.037l.09.39a.767.767 0 01.02.076c.037.147.073.296.112.442a20.188 20.188 0 00.411 1.363l.004.017c.252.728.537 1.398.856 1.986.046.085.094.169.142.25.004.003.004.007.005.01h.002c.043.075.088.148.132.219l.005.009c.049.077.098.15.148.222.007.01.012.02.02.03v.001c.066.094.132.184.198.27l.034.04c.002.007.008.01.01.017a5.367 5.367 0 00.405.452l.073.07.109.099c.028.022.055.043.083.067.034.029.07.056.105.084l.085.06.108.075c.029.016.058.034.086.052.037.023.077.044.115.067l.081.04c.045.023.09.041.135.062.023.006.046.018.07.027.067.028.136.052.206.07.047.014.087.03.125.043 3.346.885 6.606-.516 10.694 2.406" })));
};
exports.LocaleIllustration = LocaleIllustration;
//# sourceMappingURL=LocaleIllustration.js.map
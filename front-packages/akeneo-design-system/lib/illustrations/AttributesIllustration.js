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
exports.AttributesIllustration = void 0;
var react_1 = __importDefault(require("react"));
var Attributes_svg_1 = __importDefault(require("../../static/illustrations/Attributes.svg"));
var theme_1 = require("../theme");
var AttributesIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: Attributes_svg_1.default }),
        react_1.default.createElement(theme_1.BrandedPath, { d: "M133.955 156.926c.104.309.195.519.223.589.008.007-.009-.004 0 0 0 0 0 .002 0 0 .001.001 0 0 0 0-.7-6.741 3.123-9.55 4.351-14.027a1.51 1.51 0 01.04-.22c.025-.1.045-.203.061-.307.182-1.239-.325-2.681-1.286-4.182-.004-.009-.01-.016-.016-.024-.078-.125-.163-.25-.248-.375 0 0-.003-.002-.004-.005a17.584 17.584 0 00-.503-.689l-.054-.072-.149-.188c-.052-.065-.102-.131-.153-.193a51.901 51.901 0 00-.323-.384c-.037-.045-.078-.09-.118-.137-1.51-1.739-3.476-3.458-5.528-4.953 0 0-.016-.023 0 0 .271 2.536.307 5.072.066 7.101-.29 2.445-1.165 5.292-2.379 7.872-2.842-.233-5.747-.891-8.01-1.858-1.896-.809-4.065-2.085-6.139-3.598.023.21.048.419.072.629l.025.187a17.268 17.268 0 00.091.656c.022.152.044.3.07.449l.028.183a25.593 25.593 0 00.107.62c.04.218.08.435.123.651.002.016.007.031.01.048.04.195.08.385.122.578.007.04.016.078.024.117l.12.522a.816.816 0 01.026.103c.05.2.098.399.151.595l.001.003c.168.633.352 1.245.552 1.83l.005.022c.338.978.722 1.881 1.151 2.67.063.114.128.227.192.337.003.004.003.009.006.013h.001c.06.1.118.199.178.293l.002.001.007.011c.064.104.13.204.198.3v.001l.027.038.001.004a10.894 10.894 0 00.31.417l.015.021c.103.129.207.254.316.371.074.084.151.161.227.238.033.032.068.061.1.093.048.045.097.087.145.131.037.033.074.06.112.091.046.039.093.075.141.113.037.028.075.054.114.081a3.776 3.776 0 00.26.171c.052.031.106.059.157.089l.11.057c.06.028.12.054.181.08.028.011.06.027.09.038.093.037.186.069.28.095.062.018.116.038.167.057 4.501 1.189 8.882-.695 14.377 3.235" })));
};
exports.AttributesIllustration = AttributesIllustration;
//# sourceMappingURL=AttributesIllustration.js.map
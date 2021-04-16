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
exports.DeleteIllustration = void 0;
var react_1 = __importDefault(require("react"));
var Delete_svg_1 = __importDefault(require("../../static/illustrations/Delete.svg"));
var theme_1 = require("../theme");
var DeleteIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: Delete_svg_1.default }),
        react_1.default.createElement(theme_1.BrandedPath, { d: "M124.911 74.326c.03.12.06.202.067.23.003.002-.003 0 0 0-.066-2.582 1.468-3.536 2.067-5.2a.55.55 0 01.021-.082c.013-.038.024-.076.033-.115.106-.465-.045-1.027-.366-1.626 0-.004-.004-.007-.005-.01a2.8 2.8 0 00-.084-.149v-.002a5.593 5.593 0 00-.17-.277l-.02-.029-.05-.076a2.134 2.134 0 00-.054-.078l-.054-.076-.056-.079-.04-.055c-.523-.706-1.22-1.417-1.955-2.045 0 0-.005-.01 0 0 .028.972-.033 1.936-.185 2.7-.18.92-.598 1.975-1.135 2.92-1.073-.173-2.157-.509-2.989-.942-.695-.363-1.48-.912-2.224-1.548.002.08.006.16.01.241l.002.071a9.479 9.479 0 00.03.425l.005.071a7.877 7.877 0 00.021.238c.01.084.02.168.028.251a.11.11 0 00.002.018l.03.223.006.046.03.202a9.354 9.354 0 00.202.982l.002.01c.1.381.22.735.358 1.047.021.045.041.09.063.134l.002.005h.001c.018.04.04.08.058.116l.001.001.002.004c.022.041.043.082.067.12l.01.016s-.002.001 0 .001l.088.145c.006.008.012.015.016.023l.006.008a2.423 2.423 0 00.188.247l.035.04.051.053.04.037c.017.017.033.033.05.047a.41.41 0 00.041.034c.018.016.035.03.053.043l.041.031c.02.012.04.025.057.038.013.01.027.016.04.024a.712.712 0 00.067.036c.01.005.021.013.033.018.034.016.068.031.103.044.023.01.044.017.062.027 1.674.584 3.394-.003 5.365 1.651" })));
};
exports.DeleteIllustration = DeleteIllustration;
//# sourceMappingURL=DeleteIllustration.js.map
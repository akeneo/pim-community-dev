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
exports.AssociationTypesIllustration = void 0;
var react_1 = __importDefault(require("react"));
var AssociationTypes_svg_1 = __importDefault(require("../../static/illustrations/AssociationTypes.svg"));
var theme_1 = require("../theme");
var AssociationTypesIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: AssociationTypes_svg_1.default }),
        react_1.default.createElement(theme_1.BrandedPath, { d: "M175.258 157.401c.04.118.075.198.086.226.003.002-.004-.002 0 0h-.001c-.268-2.583 1.197-3.66 1.668-5.375a.482.482 0 01.015-.084 1.19 1.19 0 00.023-.119c.07-.474-.124-1.026-.492-1.602l-.006-.009a3.245 3.245 0 00-.096-.143l-.001-.002a7.002 7.002 0 00-.192-.264c-.008-.01-.014-.019-.022-.027l-.056-.073-.06-.074-.06-.072-.063-.075a1.042 1.042 0 00-.045-.052c-.58-.667-1.332-1.325-2.118-1.899 0 0-.006-.008 0 0 .104.972.117 1.944.025 2.721-.111.938-.447 2.028-.912 3.017-1.09-.089-2.201-.342-3.07-.712-.725-.31-1.557-.799-2.351-1.379.008.081.018.16.028.241l.01.071c.006.058.014.116.023.173l.01.08.027.171.012.071a13.734 13.734 0 00.088.486c0 .007.003.013.004.02a7.173 7.173 0 00.055.265l.047.2.01.04c.018.075.037.153.058.228v.001c.064.242.134.476.21.701 0 .003.003.006.003.008.129.376.277.72.44 1.023l.075.13c0 .001 0 .003.002.004.022.04.046.076.068.112v.001l.004.004.075.115.01.015.002.001c.032.05.068.095.1.14a.262.262 0 01.018.02l.006.01c.039.048.079.096.12.141l.088.09.038.037.056.05.043.035a.988.988 0 00.098.074l.056.04.043.025c.02.012.04.024.06.034l.043.022c.022.012.046.021.069.031l.034.015c.036.014.072.026.108.036.024.007.045.015.064.022 1.724.455 3.403-.266 5.509 1.24" })));
};
exports.AssociationTypesIllustration = AssociationTypesIllustration;
//# sourceMappingURL=AssociationTypesIllustration.js.map
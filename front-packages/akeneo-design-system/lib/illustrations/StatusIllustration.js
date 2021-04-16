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
exports.StatusIllustration = void 0;
var react_1 = __importDefault(require("react"));
var Status_svg_1 = __importDefault(require("../../static/illustrations/Status.svg"));
var theme_1 = require("../theme");
var StatusIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: Status_svg_1.default }),
        react_1.default.createElement("g", { fill: "none", fillRule: "evenodd" },
            react_1.default.createElement(theme_1.BrandedPath, { d: "M137.275 100.23c.078.228.146.385.166.436.007.005-.007-.002 0 0 0 0 0 .001 0 0 .001.001 0 0 0 0-.52-5.013 2.322-7.103 3.236-10.432.005-.05.015-.104.03-.165.02-.074.034-.152.046-.228.135-.922-.243-1.994-.957-3.11-.003-.007-.008-.013-.01-.019a10.36 10.36 0 00-.187-.278l-.002-.004c-.117-.17-.243-.34-.375-.513-.012-.017-.025-.036-.04-.053-.036-.046-.075-.093-.11-.14a10.482 10.482 0 00-.114-.144l-.119-.14-.12-.146a3.8 3.8 0 00-.09-.1c-1.123-1.295-2.585-2.573-4.11-3.686 0 0-.013-.017 0 0 .201 1.887.227 3.774.048 5.283-.216 1.818-.867 3.936-1.77 5.855-2.114-.173-4.274-.663-5.959-1.382-1.409-.6-3.022-1.55-4.565-2.677.017.157.035.312.054.468l.018.14.045.334a33.437 33.437 0 00.073.488l.023.136a20.47 20.47 0 00.08.461c.027.162.06.323.091.483.001.013.005.025.008.037.03.145.058.286.09.43.005.03.012.058.018.086l.09.39a.527.527 0 01.018.076c.038.148.074.297.112.443.002 0 .001.002.001.002.126.471.262.925.41 1.361.002.006.005.01.006.016.25.73.536 1.398.854 1.986.048.084.096.17.144.25.002.003.002.007.005.01.044.074.088.15.133.22l.005.008a4.504 4.504 0 00.168.252l.001.002a7.497 7.497 0 00.23.31l.012.017a4.849 4.849 0 00.404.452l.074.07.108.097c.027.024.055.045.083.068l.105.083c.028.022.056.041.085.061l.108.075.085.053.117.065c.027.015.055.03.081.042.045.022.09.041.135.061.022.007.045.02.068.027.07.028.138.051.208.07.047.015.087.03.124.044 3.347.884 6.606-.517 10.694 2.405" }))));
};
exports.StatusIllustration = StatusIllustration;
//# sourceMappingURL=StatusIllustration.js.map
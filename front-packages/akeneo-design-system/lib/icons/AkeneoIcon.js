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
exports.AkeneoIcon = void 0;
var react_1 = __importDefault(require("react"));
var AkeneoIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("path", { d: "M22.716 11.306a.91.91 0 01.036-.194c.017-.088.053-.176.053-.265.194-1.058-.195-2.329-.99-3.652a3.992 3.992 0 01-.23-.353 9.764 9.764 0 00-.424-.617c-.017-.018-.035-.036-.053-.071-.035-.053-.088-.106-.123-.159-.036-.053-.089-.123-.124-.176-.035-.053-.088-.106-.124-.159-.053-.053-.088-.124-.141-.176-.035-.036-.07-.089-.106-.124-1.255-1.553-2.898-3.088-4.63-4.429.16 2.188.124 4.394-.141 6.123-.318 2.1-1.166 4.534-2.28 6.722-2.456-.282-4.93-.935-6.873-1.835-1.608-.758-3.446-1.923-5.195-3.282l.053.547c0 .053.017.106.017.16.018.123.018.264.036.387 0 .053.017.124.017.177.018.123.036.265.053.388 0 .053.018.106.018.159.018.159.035.335.07.494.036.247.054.423.089.617.035.212.053.37.088.53 0 .035.018.07.018.106.035.158.053.3.088.458 0 .036.018.053.018.089a20.827 20.827 0 00.53 2.1c.265.864.566 1.658.919 2.346.053.106.106.194.159.3.053.106.088.194.141.282.053.106.106.194.16.282.088.142.158.247.23.353.034.053.034.07.052.07.088.107.177.23.265.336.07.07.124.141.195.212l.088.088c.035.035.088.07.124.124l.088.088c.035.035.07.07.124.106.035.017.07.053.088.07.035.036.088.07.124.089.035.017.07.035.088.07.035.035.088.053.141.088.036.018.071.036.089.053.053.018.106.053.159.07.017.018.053.018.07.036.071.035.16.07.23.088.053.018.106.036.142.053 3.852 1.147 7.775-.335 12.404 3.212-.406-5.84 2.969-8.152 4.17-11.98z", fill: color, fillRule: "evenodd" })));
};
exports.AkeneoIcon = AkeneoIcon;
//# sourceMappingURL=AkeneoIcon.js.map
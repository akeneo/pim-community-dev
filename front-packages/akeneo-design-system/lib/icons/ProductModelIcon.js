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
exports.ProductModelIcon = void 0;
var react_1 = __importDefault(require("react"));
var ProductModelIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("path", { d: "M8 14.25a.25.25 0 11.5 0 .25.25 0 01-.5 0zm2 0a.25.25 0 11.5 0 .25.25 0 01-.5 0zm2 0a.25.25 0 11.5 0 .25.25 0 01-.5 0zm2 0a.25.25 0 11.5 0 .25.25 0 01-.5 0zm2 0a.25.25 0 11.5 0 .25.25 0 01-.5 0zM7.5 9h3v3h-3V9zm9-4h2.7c.442 0 .8.544.8 1.214v14.572c0 .67-.358 1.214-.8 1.214H4.8c-.442 0-.8-.544-.8-1.214V6.214C4 5.544 4.358 5 4.8 5h2.7m2-2h4.8v4H9.5V3zm-2 16h2m-2-2.5v2.354V16.5zm7 2.5h2m0-2.5v2.354V16.5zm-2-7.5h2m0 .5v2-2z", stroke: color, fill: "none", fillRule: "evenodd", strokeLinecap: "round", strokeLinejoin: "round" })));
};
exports.ProductModelIcon = ProductModelIcon;
//# sourceMappingURL=ProductModelIcon.js.map
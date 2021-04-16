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
exports.GiftIcon = void 0;
var react_1 = __importDefault(require("react"));
var GiftIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("g", { transform: "translate(2 2)", stroke: color, fill: "none", fillRule: "evenodd", strokeLinecap: "round", strokeLinejoin: "round" },
            react_1.default.createElement("rect", { x: 1.5, y: 9.5, width: 17, height: 10, rx: 1 }),
            react_1.default.createElement("rect", { x: 0.5, y: 5.5, width: 19, height: 4, rx: 1 }),
            react_1.default.createElement("path", { d: "M10.133 5.665C8.43 1.135 6.927-.317 5.63 1.309c-1.617 2.026-.116 3.478 4.504 4.356z" }),
            react_1.default.createElement("path", { d: "M10 5.665c1.705-4.53 3.206-5.982 4.505-4.356 1.617 2.026.115 3.478-4.505 4.356zM10 5.361v13.973" }))));
};
exports.GiftIcon = GiftIcon;
//# sourceMappingURL=GiftIcon.js.map
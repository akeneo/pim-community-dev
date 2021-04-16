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
exports.InfoRoundIcon = void 0;
var react_1 = __importDefault(require("react"));
var InfoRoundIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("g", { fill: "none", fillRule: "evenodd" },
            react_1.default.createElement("circle", { stroke: color, strokeLinecap: "round", strokeLinejoin: "round", cx: 12, cy: 12, r: 10 }),
            react_1.default.createElement("path", { d: "M14.057 9.54l-2.155 7.016c-.12.401-.179.664-.179.79 0 .072.032.14.095.202s.13.093.2.093c.119 0 .238-.049.357-.148.315-.243.694-.684 1.136-1.322l.357.197c-1.058 1.73-2.183 2.596-3.375 2.596-.456 0-.818-.12-1.088-.36A1.169 1.169 0 019 17.69c0-.244.06-.553.179-.928l1.461-4.717c.14-.454.21-.796.21-1.026 0-.145-.066-.273-.2-.385-.132-.112-.315-.168-.546-.168a8.66 8.66 0 00-.379.01l.137-.395 3.564-.543h.631zM13.405 5c.435 0 .801.141 1.1.424.297.283.446.625.446 1.027 0 .401-.15.743-.452 1.026a1.54 1.54 0 01-1.094.424c-.42 0-.781-.141-1.083-.424a1.356 1.356 0 01-.452-1.026c0-.402.15-.744.447-1.027.298-.283.66-.424 1.088-.424z", fill: color }))));
};
exports.InfoRoundIcon = InfoRoundIcon;
//# sourceMappingURL=InfoRoundIcon.js.map
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
exports.ClockIcon = void 0;
var react_1 = __importDefault(require("react"));
var ClockIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("g", { fill: "none", fillRule: "evenodd" },
            react_1.default.createElement("path", { d: "M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm0-9.47V6.673m0 5.859l3.557-1.475L12 12.53z", stroke: color, strokeLinecap: "round", strokeLinejoin: "round" }),
            react_1.default.createElement("path", { d: "M12 19.5a.5.5 0 110 1 .5.5 0 010-1zm-5.657-2.55a.5.5 0 110 1 .5.5 0 010-1zm11.595 0a.5.5 0 110 1 .5.5 0 010-1zM20 11.5a.5.5 0 110 1 .5.5 0 010-1zm-16 0a.5.5 0 110 1 .5.5 0 010-1zm13.657-5.864a.5.5 0 110 1 .5.5 0 010-1zm-11.032 0a.5.5 0 110 1 .5.5 0 010-1zM12 3.5a.5.5 0 110 1 .5.5 0 010-1z", fill: color }))));
};
exports.ClockIcon = ClockIcon;
//# sourceMappingURL=ClockIcon.js.map
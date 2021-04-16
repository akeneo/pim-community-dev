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
exports.CreditsIcon = void 0;
var react_1 = __importDefault(require("react"));
var CreditsIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("path", { d: "M12 5v8.063m-5.013 5.872C5.268 18.711 4 17.93 4 17V5m0 10c0 .933 1.278 1.717 3.006 1.938M4 13c0 .938 1.292 1.725 3.035 1.941M4 11c0 1.105 1.79 2 4 2h0c2.21 0 4-.895 4-2M4 9c0 1.105 1.79 2 4 2h0c2.21 0 4-.895 4-2M4 7c0 1.105 1.79 2 4 2h0c2.21 0 4-.895 4-2M8 7c2.21 0 4-.895 4-2s-1.79-2-4-2-4 .895-4 2 1.79 2 4 2zm6.973 10.933c.328.044.672.067 1.027.067 2.21 0 4-.895 4-2v-6m-5.032 5.933c.33.044.675.067 1.032.067 2.21 0 4-.895 4-2m0-2c0 1.105-1.79 2-4 2-.513 0-1.268-.095-1.718-.183m-1.77-.696C12.06 12.85 12 12.36 12 12v-2m4 2c2.21 0 4-.895 4-2s-1.79-2-4-2-4 .895-4 2 1.79 2 4 2zm-1 3v4c0 1.105-1.79 2-4 2s-4-.895-4-2v-4m0 2c0 1.105 1.79 2 4 2h0c2.21 0 4-.895 4-2m-4 0c2.21 0 4-.895 4-2s-1.79-2-4-2-4 .895-4 2 1.79 2 4 2z", stroke: color, fill: "none", fillRule: "evenodd", strokeLinecap: "round" })));
};
exports.CreditsIcon = CreditsIcon;
//# sourceMappingURL=CreditsIcon.js.map
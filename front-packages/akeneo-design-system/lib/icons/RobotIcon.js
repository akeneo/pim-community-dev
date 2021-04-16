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
exports.RobotIcon = void 0;
var react_1 = __importDefault(require("react"));
var RobotIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("g", { fill: "none", fillRule: "evenodd" },
            react_1.default.createElement("path", { d: "M15 8a5.5 5.5 0 014.93 3.059c.898.244 1.57 1.245 1.57 2.441 0 1.196-.672 2.197-1.57 2.442A5.5 5.5 0 0115 19H9a5.5 5.5 0 01-4.93-3.059c-.898-.244-1.57-1.245-1.57-2.441 0-1.196.672-2.197 1.57-2.442A5.5 5.5 0 019 8h6zm-6 2h6a3.5 3.5 0 010 7H9a3.5 3.5 0 010-7zm-.7 3.25c.427-.333.855-.5 1.282-.5.428 0 .855.167 1.282.5m2.436 0c.427-.333.855-.5 1.282-.5.428 0 .855.167 1.282.5", stroke: color, strokeLinecap: "round", strokeLinejoin: "round" }),
            react_1.default.createElement("path", { d: "M11.38 5.292c.07 0 .13.059.13.129v.258c0 .334.356.38.486.385h.031c.088 0 .52-.017.52-.385V5.42a.13.13 0 01.129-.129h.778c.07 0 .13.059.13.129v.258c0 .66-.422 1.173-1.056 1.35v.864c0 .088-.157.16-.349.16h-.34c-.191 0-.348-.072-.348-.16l-.001-.875c-.612-.186-1.018-.693-1.018-1.34v-.257a.13.13 0 01.13-.129h.778zM13.446 4c.07 0 .13.058.13.129v.773a.13.13 0 01-.13.129h-.778a.13.13 0 01-.13-.13V4.13a.13.13 0 01.13-.129h.778zm-2.074 0c.07 0 .13.058.13.129v.773a.13.13 0 01-.13.129h-.778a.13.13 0 01-.13-.13V4.13a.13.13 0 01.13-.129h.778z", fill: color }))));
};
exports.RobotIcon = RobotIcon;
//# sourceMappingURL=RobotIcon.js.map
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
exports.InfoIcon = void 0;
var react_1 = __importDefault(require("react"));
var InfoIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("path", { d: "M14.651 9l-2.835 9.228c-.156.528-.235.874-.235 1.038a.37.37 0 00.125.266c.083.082.17.124.263.124.156 0 .313-.065.47-.195.415-.32.912-.9 1.493-1.74l.47.26c-1.392 2.276-2.871 3.414-4.438 3.414-.6 0-1.077-.158-1.432-.474A1.537 1.537 0 018 19.72c0-.32.078-.727.235-1.22l1.922-6.204c.185-.597.277-1.047.277-1.35 0-.19-.088-.36-.263-.506-.175-.147-.415-.221-.72-.221-.137 0-.303.004-.497.013l.18-.52L13.82 9h.83zm-.632-6c.571 0 1.053.186 1.445.558.392.372.588.822.588 1.35 0 .528-.199.978-.595 1.35a2.026 2.026 0 01-1.438.558 2.008 2.008 0 01-1.424-.558A1.784 1.784 0 0112 4.908c0-.528.196-.978.588-1.35A2.001 2.001 0 0114.018 3z", fill: color, fillRule: "evenodd" })));
};
exports.InfoIcon = InfoIcon;
//# sourceMappingURL=InfoIcon.js.map
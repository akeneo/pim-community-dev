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
exports.ExplanationPointIcon = void 0;
var react_1 = __importDefault(require("react"));
var ExplanationPointIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("path", { d: "M12.338 15.38h-.66c-.07-1.074-.301-2.376-.692-3.903l-.78-3.084C9.736 6.545 9.5 5.22 9.5 4.42c0-.726.23-1.311.69-1.754.461-.443 1.062-.665 1.802-.665.721 0 1.32.224 1.795.672.475.448.713 1.016.713 1.704 0 .717-.25 2.056-.75 4.017l-.797 3.084c-.28 1.093-.485 2.395-.615 3.903zM12 18a1.93 1.93 0 011.414.585c.39.39.586.863.586 1.421 0 .55-.195 1.02-.586 1.41A1.93 1.93 0 0112 22a1.93 1.93 0 01-1.414-.585c-.39-.39-.586-.86-.586-1.409 0-.55.193-1.021.58-1.415.387-.394.86-.591 1.42-.591z", fill: color, fillRule: "evenodd" })));
};
exports.ExplanationPointIcon = ExplanationPointIcon;
//# sourceMappingURL=ExplanationPointIcon.js.map
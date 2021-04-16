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
exports.EditIcon = void 0;
var react_1 = __importDefault(require("react"));
var EditIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("g", { stroke: color, fill: "none", fillRule: "evenodd", strokeLinecap: "round" },
            react_1.default.createElement("path", { d: "M6.5 6h3M6.5 14.5h1M14.984 14.5h2.126M6.5 18h10.605" }),
            react_1.default.createElement("path", { strokeLinejoin: "round", d: "M9.32 10.368l8.606-7.749 3.011 3.344-8.606 7.75-3.315.045z" }),
            react_1.default.createElement("path", { d: "M11 10.5l1.237 1.445" }),
            react_1.default.createElement("path", { strokeLinejoin: "round", d: "M21 9.3V22H3V2h11" }))));
};
exports.EditIcon = EditIcon;
//# sourceMappingURL=EditIcon.js.map
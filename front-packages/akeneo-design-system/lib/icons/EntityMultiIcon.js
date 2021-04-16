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
exports.EntityMultiIcon = void 0;
var react_1 = __importDefault(require("react"));
var EntityMultiIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("path", { d: "M20 8.074V21a1 1 0 01-1 1H5a1 1 0 01-1-1V3a1 1 0 011-1h9m.413 9H16a1 1 0 011 1v6a1 1 0 01-1 1h-1.587a1 1 0 01-1-1v-6a1 1 0 011-1zm-6 3h1.5a1 1 0 011 1v3a1 1 0 01-1 1h-1.5a1 1 0 01-1-1v-3a1 1 0 011-1zm0-9h1.5a1 1 0 011 1v5a1 1 0 01-1 1h-1.5a1 1 0 01-1-1V6a1 1 0 011-1zM17 2v6m-3-3h6", stroke: color, fill: "none", fillRule: "evenodd", strokeLinecap: "round", strokeLinejoin: "round" })));
};
exports.EntityMultiIcon = EntityMultiIcon;
//# sourceMappingURL=EntityMultiIcon.js.map
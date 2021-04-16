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
exports.HelpIcon = void 0;
var react_1 = __importDefault(require("react"));
var HelpIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("g", { transform: "translate(2 2)", fill: "none", fillRule: "evenodd" },
            react_1.default.createElement("path", { d: "M9.972 14.465a.748.748 0 11-.002 1.496.748.748 0 01.002-1.496zM9.986 4a3.498 3.498 0 013.486 3.487c.01 1.507-.883 2.44-1.642 3.159-.38.36-.733.684-.972 1.014-.24.33-.374.649-.374 1.077v.25a.5.5 0 01-.749.437.5.5 0 01-.247-.438v-.25c0-.658.244-1.215.568-1.661.324-.446.72-.804 1.09-1.154.74-.702 1.34-1.305 1.33-2.426a2.486 2.486 0 00-2.49-2.497 2.486 2.486 0 00-2.49 2.497.5.5 0 01-.749.438.5.5 0 01-.247-.438A3.498 3.498 0 019.986 4z", fill: color }),
            react_1.default.createElement("circle", { stroke: color, strokeLinecap: "round", strokeLinejoin: "round", cx: 10, cy: 10, r: 10 }))));
};
exports.HelpIcon = HelpIcon;
//# sourceMappingURL=HelpIcon.js.map
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
exports.LinkIcon = void 0;
var react_1 = __importDefault(require("react"));
var LinkIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("path", { stroke: color, d: "M8.442 11.347c-.535.535-1.42.517-1.977-.04L2.43 7.272c-.557-.557-.575-1.442-.04-1.977L5.295 2.39c.535-.535 1.42-.517 1.977.04l4.035 4.035c.557.557.575 1.442.04 1.977m3.632 3.632c.535-.535 1.42-.517 1.977.04h0l4.035 4.035c.557.557.575 1.443.04 1.977l-2.905 2.906c-.534.534-1.42.516-1.977-.04l-4.035-4.036c-.557-.557-.575-1.442-.04-1.977m-3.39-6.295l6.053 6.053", fill: "none", fillRule: "evenodd", strokeLinecap: "round", strokeLinejoin: "round" })));
};
exports.LinkIcon = LinkIcon;
//# sourceMappingURL=LinkIcon.js.map
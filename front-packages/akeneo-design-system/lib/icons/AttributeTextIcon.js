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
exports.AttributeTextIcon = void 0;
var react_1 = __importDefault(require("react"));
var AttributeTextIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("defs", null,
            react_1.default.createElement("path", { d: "M15 14l-3.522-8L8 14h7zM2 22v-.537c.744-.087 1.303-.377 1.675-.87.372-.493 1.01-1.832 1.915-4.017L11.625 2h.566l7.208 16.838c.48 1.122.865 1.816 1.152 2.082.288.265.77.447 1.449.543V22h-7.364v-.537c.848-.077 1.395-.171 1.64-.282.245-.112.367-.385.367-.82 0-.145-.047-.401-.141-.769a8.51 8.51 0 00-.396-1.16l-1.201-2.857H7.329c-.754 1.944-1.204 3.13-1.35 3.56-.146.43-.22.772-.22 1.023 0 .503.199.85.594 1.044.245.116.707.203 1.386.261V22H2z", id: "prefix__AttributeTextIcon" })),
        react_1.default.createElement("use", { fill: color, xlinkHref: "#prefix__AttributeTextIcon", fillRule: "evenodd" })));
};
exports.AttributeTextIcon = AttributeTextIcon;
//# sourceMappingURL=AttributeTextIcon.js.map
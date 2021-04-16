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
exports.FileCsvIcon = void 0;
var react_1 = __importDefault(require("react"));
var FileCsvIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("g", { fill: "none", fillRule: "evenodd" },
            react_1.default.createElement("path", { d: "M6.5 18h10.605M6.5 14h10.605M3 2h18v20H3V2z", stroke: color, strokeLinecap: "round", strokeLinejoin: "round" }),
            react_1.default.createElement("path", { d: "M7.988 6c.362 0 .682.055.959.166.276.11.52.261.73.45l-.226.303a.181.181 0 01-.055.055.164.164 0 01-.09.022c-.044 0-.096-.022-.157-.066a1.924 1.924 0 00-.614-.295 1.966 1.966 0 00-.55-.066c-.263 0-.503.044-.72.13a1.575 1.575 0 00-.563.379c-.158.165-.28.366-.368.603a2.308 2.308 0 00-.131.803c0 .303.045.573.136.81.092.238.216.438.373.602.158.164.343.289.557.375.215.086.446.128.694.128.152 0 .288-.008.41-.025a1.74 1.74 0 00.338-.08c.102-.036.198-.082.287-.137.089-.055.177-.121.264-.198.04-.034.08-.051.121-.051.038 0 .071.015.1.044l.273.281c-.208.23-.461.41-.758.54-.297.128-.656.193-1.077.193-.365 0-.696-.06-.994-.182a2.19 2.19 0 01-.763-.508 2.28 2.28 0 01-.49-.785A2.812 2.812 0 015.5 8.484c0-.366.06-.701.181-1.006.12-.305.29-.567.51-.787.218-.219.48-.389.786-.51.305-.12.642-.181 1.011-.181zm3.939 0c.284 0 .542.043.775.129.233.086.439.21.616.373l-.167.311a.253.253 0 01-.069.077.157.157 0 01-.09.025.26.26 0 01-.142-.057 2.495 2.495 0 00-.204-.128 1.68 1.68 0 00-.295-.127 1.323 1.323 0 00-.417-.057c-.154 0-.29.02-.407.059a.881.881 0 00-.294.161.663.663 0 00-.18.239.729.729 0 00-.06.297.53.53 0 00.105.337c.07.089.162.165.277.229.115.063.245.118.39.164l.447.144c.153.05.302.106.448.168.145.062.275.14.39.235.115.095.207.212.277.35.07.137.105.307.105.508 0 .212-.038.412-.114.598a1.375 1.375 0 01-.332.487 1.563 1.563 0 01-.536.325c-.211.079-.452.119-.722.119-.331 0-.632-.058-.902-.172-.27-.114-.5-.268-.692-.462l.199-.312a.257.257 0 01.07-.063.168.168 0 01.09-.025c.03 0 .065.012.104.035.04.024.084.054.134.09s.106.076.17.119a1.427 1.427 0 00.497.208c.104.024.221.036.351.036.164 0 .31-.022.437-.065a.938.938 0 00.325-.18.786.786 0 00.204-.28.898.898 0 00.07-.363.589.589 0 00-.104-.361.856.856 0 00-.275-.236 1.96 1.96 0 00-.39-.16 92.21 92.21 0 01-.448-.136c-.151-.047-.3-.1-.447-.161a1.455 1.455 0 01-.39-.238 1.094 1.094 0 01-.276-.364 1.247 1.247 0 01-.104-.54c0-.172.035-.338.104-.499.07-.16.172-.303.306-.427.133-.124.298-.224.493-.298.195-.075.42-.112.673-.112zm2.308.057a.236.236 0 01.235.156l1.434 3.427a4.143 4.143 0 01.17.536c.024-.1.049-.195.075-.285.026-.09.054-.174.085-.25l1.43-3.428a.25.25 0 01.084-.106.236.236 0 01.15-.05h.55l-2.072 4.858h-.618l-2.073-4.858h.55z", fill: color }))));
};
exports.FileCsvIcon = FileCsvIcon;
//# sourceMappingURL=FileCsvIcon.js.map
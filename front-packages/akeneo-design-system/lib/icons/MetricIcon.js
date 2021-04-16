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
exports.MetricIcon = void 0;
var react_1 = __importDefault(require("react"));
var MetricIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("g", { fill: "none", fillRule: "evenodd" },
            react_1.default.createElement("path", { d: "M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm0-10.5v-5", stroke: color, strokeLinecap: "round", strokeLinejoin: "round" }),
            react_1.default.createElement("path", { d: "M10 18a.5.5 0 110 1 .5.5 0 010-1zm2 0a.5.5 0 110 1 .5.5 0 010-1zm2 0a.5.5 0 110 1 .5.5 0 010-1zm-2-8.5a2 2 0 110 4 2 2 0 010-4zm8.435 1.44c.043.35.065.704.065 1.06a.5.5 0 11-1 0c0-.315-.02-.628-.058-.936a.5.5 0 11.993-.124zm-16.313-.426a.5.5 0 01.435.558 7.578 7.578 0 00-.057.912.5.5 0 01-1-.002c0-.347.022-.692.064-1.033a.5.5 0 01.558-.435zm1.672-3.588a.5.5 0 01.114.698c-.182.254-.35.519-.499.794a.5.5 0 11-.878-.479c.17-.311.359-.611.566-.9a.5.5 0 01.697-.113zm13.073.063c.209.286.4.585.572.895a.5.5 0 11-.875.485 7.507 7.507 0 00-.505-.79.5.5 0 11.808-.59zM9.024 4.555a.5.5 0 01-.237.666 7.485 7.485 0 00-.82.455.5.5 0 11-.539-.843c.299-.191.61-.363.93-.515a.5.5 0 01.666.237zm6.56-.265c.321.15.633.32.933.508a.5.5 0 01-.532.847 7.482 7.482 0 00-.823-.448.5.5 0 01.422-.907zm-3.575-.79c.173 0 .346.006.517.016a.5.5 0 01-.06.998 7.618 7.618 0 00-.947.001.5.5 0 01-.063-.998 8.7 8.7 0 01.553-.017z", fill: color }))));
};
exports.MetricIcon = MetricIcon;
//# sourceMappingURL=MetricIcon.js.map
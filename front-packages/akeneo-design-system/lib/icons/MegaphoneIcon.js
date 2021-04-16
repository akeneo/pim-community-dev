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
exports.MegaphoneIcon = void 0;
var react_1 = __importDefault(require("react"));
var MegaphoneIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("path", { d: "M3.185 12.521a2.369 2.369 0 002.369 4.103l4.01-2.315 8.048-1.133a1 1 0 00.726-1.49l-4.52-7.828a1 1 0 00-1.652-.118L7.09 10.206 3.185 12.52zm4.648 2.75l2.1 3.637c.497.86.318 1.894-.4 2.308-.717.415-1.701.052-2.198-.808l-2.1-3.638h0m12.078-3.564l-5.394-9.23m5.168 5.561c.556-.355 1.046-1.123 1.046-1.831a2 2 0 00-2-2c-.42 0-.77.07-1.091.291M7.088 10.15l2.476 4.159m10.361-8.532l1.347-.695M17.229 3.63L17.77 2m2.468 7l1.663.427", stroke: color, fill: "none", fillRule: "evenodd", strokeLinecap: "round", strokeLinejoin: "round" })));
};
exports.MegaphoneIcon = MegaphoneIcon;
//# sourceMappingURL=MegaphoneIcon.js.map
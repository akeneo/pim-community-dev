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
exports.AttributeNumberIcon = void 0;
var react_1 = __importDefault(require("react"));
var AttributeNumberIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("defs", null,
            react_1.default.createElement("path", { d: "M9.517 2c.039.049.06.085.065.11a.738.738 0 01.007.107v17.585c0 .747.2 1.223.597 1.427.398.203 1.14.325 2.227.363V22h-8.21v-.437c1.175-.058 1.942-.218 2.3-.48.36-.262.54-.83.54-1.703V5.9c0-.466-.059-.82-.175-1.063-.117-.242-.37-.363-.757-.363-.253 0-.58.07-.983.21A14.8 14.8 0 004 5.13v-.408L9.342 2h.175zm6.256 4.227c1.146 0 2.032.457 2.657 1.37.625.915.937 1.934.937 3.06 0 .79-.148 1.596-.445 2.413a6.729 6.729 0 01-1.297 2.188c-.661.74-1.5 1.289-2.516 1.648-.567.203-1.283.36-2.148.469l-.078-.313a9.878 9.878 0 001.351-.398c.646-.255 1.164-.568 1.555-.937a6.51 6.51 0 001.324-1.762c.331-.649.538-1.192.621-1.63l-.273.204a3.365 3.365 0 01-1.36.602c-.27.062-.518.093-.742.093-.885 0-1.587-.313-2.105-.941-.518-.628-.777-1.376-.777-2.246 0-1.11.308-2.024.925-2.742.618-.72 1.408-1.078 2.371-1.078zm-.109.445c-.51 0-.923.232-1.238.695-.315.464-.473 1.156-.473 2.078 0 .76.147 1.464.442 2.11.294.646.826.968 1.597.968.354 0 .72-.099 1.098-.296.377-.198.592-.365.644-.5.021-.053.04-.247.055-.582.016-.336.023-.609.023-.817 0-1.083-.182-1.963-.546-2.64-.365-.678-.899-1.016-1.602-1.016z", id: "prefix__AttributeNumberIcon" })),
        react_1.default.createElement("use", { fill: color, xlinkHref: "#prefix__AttributeNumberIcon", fillRule: "evenodd" })));
};
exports.AttributeNumberIcon = AttributeNumberIcon;
//# sourceMappingURL=AttributeNumberIcon.js.map
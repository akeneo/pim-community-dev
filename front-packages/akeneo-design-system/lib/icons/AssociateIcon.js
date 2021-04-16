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
exports.AssociateIcon = void 0;
var react_1 = __importDefault(require("react"));
var AssociateIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("path", { d: "M15 6h2.7c.442 0 .8.544.8 1.214v14.572c0 .67-.358 1.214-.8 1.214H3.3c-.442 0-.8-.544-.8-1.214V7.214c0-.67.358-1.214.8-1.214H6m2-2h4.8v4H8V4zM6.5 17.53l8-.06m-8-2.94l8-.06m-8-2.94l8-.06m2-6.97h2.7c.442 0 .8.544.8 1.214v14.572c0 .67-.358 1.214-.8 1.214M4 5.714c0-.67.358-1.214.8-1.214h2.7m2-2h4.8v2m-4.8-.616V2.5M18 3h2.7c.442 0 .8.544.8 1.214v14.572c0 .67-.358 1.214-.8 1.214M5.5 4.214c0-.67.358-1.214.8-1.214H9m2-2h4.8v2M11 2.384V1", stroke: color, fill: "none", fillRule: "evenodd", strokeLinecap: "round", strokeLinejoin: "round" })));
};
exports.AssociateIcon = AssociateIcon;
//# sourceMappingURL=AssociateIcon.js.map
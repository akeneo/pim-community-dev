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
exports.AttributeTextareaIcon = void 0;
var react_1 = __importDefault(require("react"));
var AttributeTextareaIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("g", { fill: "none", fillRule: "evenodd" },
            react_1.default.createElement("path", { d: "M2.5 18.5v3m0 0h3m16-19v3m-3-3h3", stroke: color, strokeLinecap: "round", strokeLinejoin: "round" }),
            react_1.default.createElement("path", { d: "M11.5 21a.5.5 0 110 1 .5.5 0 010-1zm2 0a.5.5 0 110 1 .5.5 0 010-1zm-6 0a.5.5 0 110 1 .5.5 0 010-1zm2 0a.5.5 0 110 1 .5.5 0 010-1zm6 0a.5.5 0 110 1 .5.5 0 010-1zm2 0a.5.5 0 110 1 .5.5 0 010-1zm2 0a.5.5 0 110 1 .5.5 0 010-1zm2 0a.5.5 0 110 1 .5.5 0 010-1zm0-2a.5.5 0 110 1 .5.5 0 010-1zM16.907 6L17 9.082h-.415c-.204-.968-.462-1.599-.776-1.89-.313-.293-.973-.44-1.978-.44h-.967v9.282c0 .703.105 1.138.314 1.306.209.168.667.28 1.373.332V18H9.49v-.328c.735-.059 1.193-.186 1.373-.38.181-.195.272-.685.272-1.47v-9.07h-.967c-.96 0-1.614.145-1.961.435-.348.289-.61.92-.784 1.895H7L7.102 6h9.805zM21.5 17a.5.5 0 110 1 .5.5 0 010-1zm-19-1a.5.5 0 110 1 .5.5 0 010-1zm19-1a.5.5 0 110 1 .5.5 0 010-1zm-19-1a.5.5 0 110 1 .5.5 0 010-1zm19-1a.5.5 0 110 1 .5.5 0 010-1zm-19-1a.5.5 0 110 1 .5.5 0 010-1zm19-1a.5.5 0 110 1 .5.5 0 010-1zm-19-1a.5.5 0 110 1 .5.5 0 010-1zm19-1a.5.5 0 110 1 .5.5 0 010-1zm-19-1a.5.5 0 110 1 .5.5 0 010-1zm19-1a.5.5 0 110 1 .5.5 0 010-1zm-19-1a.5.5 0 110 1 .5.5 0 010-1zm0-2a.5.5 0 110 1 .5.5 0 010-1zm0-2a.5.5 0 110 1 .5.5 0 010-1zm2 0a.5.5 0 110 1 .5.5 0 010-1zm2 0a.5.5 0 110 1 .5.5 0 010-1zm4 0a.5.5 0 110 1 .5.5 0 010-1zm6 0a.5.5 0 110 1 .5.5 0 010-1zm-8 0a.5.5 0 110 1 .5.5 0 010-1zm4 0a.5.5 0 110 1 .5.5 0 010-1zm2 0a.5.5 0 110 1 .5.5 0 010-1z", fill: color }))));
};
exports.AttributeTextareaIcon = AttributeTextareaIcon;
//# sourceMappingURL=AttributeTextareaIcon.js.map
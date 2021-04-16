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
exports.ShopIcon = void 0;
var react_1 = __importDefault(require("react"));
var ShopIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (react_1.default.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("path", { d: "M2 6h20v16H2V6zm13 7h5v9h-5v-9zM5 13h7v5H5v-5zM19 2l3 4h-.645a1.6 1.6 0 01.119.605v1.79a1.605 1.605 0 01-3.154.426l-.005-.022V6.605c0-.098-.008-.195-.025-.288l.03-.138c.017-.062.038-.121.061-.18h-.183c.04.102.072.208.092.318l-.02.142h0l-.007.146v1.79c0 .098.009.195.026.288l.026.116v.596a1.605 1.605 0 01-3.152.426l-.032-.136c.01-.049.016-.096.02-.144l.007-.146v-2.79c0-.098-.009-.195-.026-.288l-.001-.003.032-.135c.016-.062.037-.121.06-.18h-.183c.023.059.044.118.06.18l.031.135c-.008.05-.015.097-.02.145l-.006.146v2.79c0 .098.01.195.026.288v.002l-.03.136a1.606 1.606 0 01-3.096 0l-.032-.136c.01-.049.016-.096.02-.144L12 9.395v-2.79c0-.098-.009-.195-.026-.288l-.001-.003.032-.135c.017-.062.037-.121.06-.18h-.183c.023.059.044.118.06.18l.031.135c-.008.05-.015.097-.02.145l-.006.146v2.79c0 .098.01.195.026.288v.002l-.03.136a1.606 1.606 0 01-3.096 0l-.032-.136c.01-.049.016-.096.02-.144l.007-.146v-2.79c0-.098-.009-.195-.026-.288l-.001-.003.032-.135c.017-.062.037-.121.06-.18h-.183c.024.059.044.118.06.18l.031.135c-.008.05-.015.097-.019.145l-.007.146v2.79c0 .098.01.195.026.288v.002l-.03.136a1.606 1.606 0 01-3.153-.426l-.001-.596.027-.116c.009-.047.015-.094.02-.142l.006-.146v-1.79c0-.098-.009-.195-.026-.288l-.001-.003.032-.135c.017-.062.037-.121.06-.18h-.183c.024.059.044.118.06.18l.031.135a2.04 2.04 0 00-.019.145l-.006.146L5.63 8.8l-.004.022a1.606 1.606 0 01-3.153-.426v-1.79A1.6 1.6 0 012.592 6L2 6l2.632-4H19z", stroke: color, fill: "none", fillRule: "evenodd", strokeLinecap: "round", strokeLinejoin: "round" })));
};
exports.ShopIcon = ShopIcon;
//# sourceMappingURL=ShopIcon.js.map
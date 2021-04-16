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
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __exportStar = (this && this.__exportStar) || function(m, exports) {
    for (var p in m) if (p !== "default" && !Object.prototype.hasOwnProperty.call(exports, p)) __createBinding(exports, m, p);
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.render = void 0;
var react_1 = __importDefault(require("react"));
var react_2 = require("@testing-library/react");
var styled_components_1 = require("styled-components");
var pim_1 = require("../theme/pim");
var wrapper = function (_a) {
    var children = _a.children;
    return react_1.default.createElement(styled_components_1.ThemeProvider, { theme: pim_1.pimTheme }, children);
};
var customRender = function (ui, options) { return react_2.render(ui, __assign({ wrapper: wrapper }, options)); };
exports.render = customRender;
__exportStar(require("@testing-library/react"), exports);
//# sourceMappingURL=test-util.js.map
"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Header = void 0;
var styled_components_1 = __importDefault(require("styled-components"));
var theme_1 = require("../../../theme");
var react_1 = __importDefault(require("react"));
var HeaderContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  box-sizing: border-box;\n  border-bottom: 1px solid ", ";\n  height: 34px;\n  line-height: 34px;\n  margin: 0 20px 10px 20px;\n"], ["\n  box-sizing: border-box;\n  border-bottom: 1px solid ", ";\n  height: 34px;\n  line-height: 34px;\n  margin: 0 20px 10px 20px;\n"])), theme_1.getColor('brand', 100));
var Header = react_1.default.forwardRef(function (_a, forwardedRef) {
    var children = _a.children;
    return react_1.default.createElement(HeaderContainer, { ref: forwardedRef }, children);
});
exports.Header = Header;
var templateObject_1;
//# sourceMappingURL=Header.js.map
"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Title = void 0;
var styled_components_1 = __importDefault(require("styled-components"));
var theme_1 = require("../../../theme");
var react_1 = __importDefault(require("react"));
var TitleContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  font-size: ", ";\n  text-transform: uppercase;\n  color: ", ";\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n"], ["\n  font-size: ", ";\n  text-transform: uppercase;\n  color: ", ";\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n"])), theme_1.getFontSize('small'), theme_1.getColor('brand', 100));
var Title = react_1.default.forwardRef(function (_a, forwardedRef) {
    var children = _a.children;
    return react_1.default.createElement(TitleContainer, { ref: forwardedRef }, children);
});
exports.Title = Title;
var templateObject_1;
//# sourceMappingURL=Title.js.map
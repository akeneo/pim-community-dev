"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.StoryStyle = void 0;
var styled_components_1 = __importDefault(require("styled-components"));
var theme_1 = require("../theme");
var StoryStyle = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  ", "\n  & > * {\n    margin: 0 10px 10px 0;\n  }\n"], ["\n  ", "\n  & > * {\n    margin: 0 10px 10px 0;\n  }\n"])), theme_1.CommonStyle);
exports.StoryStyle = StoryStyle;
var templateObject_1;
//# sourceMappingURL=global.js.map
"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.placeholderStyle = exports.BrandedPath = exports.CommonStyle = exports.palette = exports.fontSize = exports.fontFamily = exports.color = void 0;
var styled_components_1 = __importStar(require("styled-components"));
var theme_1 = require("./theme");
var CommonStyle = styled_components_1.css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  input,\n  button,\n  select,\n  textarea {\n    font-family: 'Lato';\n    font-size: ", ";\n  }\n\n  font-family: 'Lato';\n  font-size: ", ";\n  color: ", ";\n  line-height: 20px;\n  box-sizing: border-box;\n"], ["\n  input,\n  button,\n  select,\n  textarea {\n    font-family: 'Lato';\n    font-size: ", ";\n  }\n\n  font-family: 'Lato';\n  font-size: ", ";\n  color: ", ";\n  line-height: 20px;\n  box-sizing: border-box;\n"])), theme_1.getFontSize('default'), theme_1.getFontSize('default'), theme_1.getColor('grey', 120));
exports.CommonStyle = CommonStyle;
var loadingBreath = styled_components_1.keyframes(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  0% {background-position:0 50%}\n  50% {background-position:100% 50%}\n  100% {background-position:0 50%}\n"], ["\n  0% {background-position:0 50%}\n  50% {background-position:100% 50%}\n  100% {background-position:0 50%}\n"])));
var placeholderStyle = styled_components_1.css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  animation: ", " 2s infinite;\n  background: linear-gradient(270deg, #fdfdfd, #eee);\n  background-size: 400% 400%;\n  border-color: transparent;\n  border-style: none;\n  color: transparent;\n  border-radius: 3px;\n  cursor: default;\n  outline: none;\n  :hover,\n  :last-child,\n  ::placeholder {\n    color: transparent;\n  }\n  > * {\n    opacity: 0;\n  }\n"], ["\n  animation: ", " 2s infinite;\n  background: linear-gradient(270deg, #fdfdfd, #eee);\n  background-size: 400% 400%;\n  border-color: transparent;\n  border-style: none;\n  color: transparent;\n  border-radius: 3px;\n  cursor: default;\n  outline: none;\n  :hover,\n  :last-child,\n  ::placeholder {\n    color: transparent;\n  }\n  > * {\n    opacity: 0;\n  }\n"])), loadingBreath);
exports.placeholderStyle = placeholderStyle;
var color = {
    blue10: '#f5f9fc',
    blue20: '#dee9f4',
    blue40: '#bdd3e9',
    blue60: '#9bbddd',
    blue80: '#7aa7d2',
    blue100: '#5992c7',
    blue120: '#47749f',
    blue140: '#355777',
    green10: '#f0f7f1',
    green20: '#e1f0e3',
    green40: '#c2e1c7',
    green60: '#a3d1ab',
    green80: '#85c28f',
    green100: '#67b373',
    green120: '#528f5c',
    green140: '#3d6b45',
    grey20: '#f6f7fb',
    grey40: '#f0f1f3',
    grey60: '#e8ebee',
    grey80: '#c7cbd4',
    grey100: '#a1a9b7',
    grey120: '#67768a',
    grey140: '#11324d',
    purple20: '#eadcf1',
    purple40: '#d4bae3',
    purple60: '#be97d5',
    purple80: '#a974c7',
    purple100: '#9452ba',
    purple120: '#764194',
    purple140: '#58316f',
    red10: '#faefed',
    red20: '#f6dfdc',
    red40: '#eebfb9',
    red60: '#e59f95',
    red80: '#dc7f72',
    red100: '#d4604f',
    red120: '#a94c3f',
    red140: '#7f392f',
    yellow10: '#fef7ec',
    yellow20: '#fef0d9',
    yellow40: '#fde1b2',
    yellow60: '#fbd28b',
    yellow80: '#fac365',
    yellow100: '#f9b53f',
    yellow120: '#c79032',
    yellow140: '#956c25',
    brand20: '#eadcf1',
    brand40: '#d4bae3',
    brand60: '#be97d5',
    brand80: '#a974c7',
    brand100: '#9452ba',
    brand120: '#764194',
    brand140: '#58316f',
    white: '#ffffff',
};
exports.color = color;
var fontSize = {
    big: '15px',
    bigger: '17px',
    default: '13px',
    small: '11px',
    title: '28px',
};
exports.fontSize = fontSize;
var palette = {
    primary: 'green',
    secondary: 'blue',
    tertiary: 'grey',
    warning: 'yellow',
    danger: 'red',
};
exports.palette = palette;
var fontFamily = {
    default: 'Lato, "Helvetica Neue", Helvetica, Arial, sans-serif',
    monospace: 'Courier, "MS Courier New", Prestige, "Everson Mono"',
};
exports.fontFamily = fontFamily;
var BrandedPath = styled_components_1.default.path(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  fill: ", ";\n"], ["\n  fill: ", ";\n"])), theme_1.getColor('brand', 100));
exports.BrandedPath = BrandedPath;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=common.js.map
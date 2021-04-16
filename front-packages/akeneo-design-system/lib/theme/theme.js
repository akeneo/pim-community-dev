"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.getFontSize = exports.getColorForLevel = exports.getFontFamily = exports.getColor = void 0;
var getColor = function (color, gradient) { return function (_a) {
    var theme = _a.theme;
    return theme.color["" + color + (gradient !== null && gradient !== void 0 ? gradient : '')];
}; };
exports.getColor = getColor;
var getColorForLevel = function (level, gradient) { return function (_a) {
    var theme = _a.theme;
    return theme.color["" + theme.palette[level] + gradient];
}; };
exports.getColorForLevel = getColorForLevel;
var getFontSize = function (fontSize) { return function (_a) {
    var theme = _a.theme;
    return theme.fontSize[fontSize];
}; };
exports.getFontSize = getFontSize;
var getFontFamily = function (fontFamilyType) { return function (_a) {
    var theme = _a.theme;
    return theme.fontFamily[fontFamilyType];
}; };
exports.getFontFamily = getFontFamily;
//# sourceMappingURL=theme.js.map
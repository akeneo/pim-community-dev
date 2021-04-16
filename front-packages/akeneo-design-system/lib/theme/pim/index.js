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
Object.defineProperty(exports, "__esModule", { value: true });
exports.pimTheme = void 0;
var common_1 = require("../common");
var pimTheme = {
    name: 'PIM',
    color: __assign(__assign({}, common_1.color), { brand20: '#eadcf1', brand40: '#d4bae3', brand60: '#be97d5', brand80: '#a974c7', brand100: '#9452ba', brand120: '#764194', brand140: '#58316f' }),
    fontSize: common_1.fontSize,
    palette: common_1.palette,
    fontFamily: common_1.fontFamily,
};
exports.pimTheme = pimTheme;
//# sourceMappingURL=index.js.map
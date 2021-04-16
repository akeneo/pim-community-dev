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
exports.onboarderTheme = void 0;
var common_1 = require("../common");
var onboarderTheme = {
    name: 'Onboarder',
    color: __assign(__assign({}, common_1.color), { brand20: '#dbedf8', brand40: '#b7dcf2', brand60: '#93caec', brand80: '#6fb9e6', brand100: '#4ca8e0', brand120: '#3c86b3', brand140: '#2d6486' }),
    fontSize: common_1.fontSize,
    palette: common_1.palette,
    fontFamily: common_1.fontFamily,
};
exports.onboarderTheme = onboarderTheme;
//# sourceMappingURL=index.js.map
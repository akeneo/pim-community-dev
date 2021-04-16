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
exports.GroupsIllustration = void 0;
var react_1 = __importDefault(require("react"));
var Groups_svg_1 = __importDefault(require("../../static/illustrations/Groups.svg"));
var theme_1 = require("../theme");
var GroupsIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: Groups_svg_1.default }),
        react_1.default.createElement(theme_1.BrandedPath, { d: "M157.93 178.163c.079.23.147.386.167.438.007.006-.006-.002 0 0 0 0 0 .001 0 0h-.001c-.52-5.013 2.324-7.102 3.237-10.433.005-.05.015-.103.03-.163a2.21 2.21 0 00.046-.23c.136-.922-.242-1.994-.957-3.11l-.011-.018a13.39 13.39 0 00-.185-.278s-.003-.002-.003-.005a14.074 14.074 0 00-.374-.513c-.013-.017-.026-.036-.04-.052-.037-.047-.076-.094-.111-.14-.04-.05-.076-.099-.114-.144l-.118-.14-.122-.147a6.553 6.553 0 00-.09-.1c-1.122-1.293-2.583-2.572-4.11-3.685 0 0-.012-.017 0 0 .202 1.886.228 3.773.049 5.282-.216 1.82-.866 3.936-1.77 5.856-2.113-.173-4.274-.663-5.96-1.382-1.408-.602-3.021-1.552-4.564-2.678.017.158.035.313.054.469l.018.14.045.334.023.154.05.334.023.136.072.42.007.04c.029.163.06.323.092.484l.008.036c.029.145.058.286.089.43l.019.088c.029.13.06.26.09.388a.564.564 0 01.018.077c.038.148.074.297.113.443v.001c.126.471.262.927.41 1.362a13.144 13.144 0 00.86 2.002c.048.085.096.169.144.25l.005.01c.044.075.089.149.133.219l.006.008c.048.077.097.151.147.222v.001l.02.03v.002c.066.093.132.183.198.268l.034.042c.003.006.008.01.01.017a5.367 5.367 0 00.406.452l.073.069.108.099c.027.023.055.043.083.066.035.03.07.056.105.084.028.022.056.04.085.06a2.635 2.635 0 00.194.129l.116.065c.028.016.055.029.08.042.046.022.09.04.136.06.022.007.045.02.068.029.069.026.138.05.208.07.047.014.087.029.125.043 3.346.883 6.606-.517 10.693 2.405" })));
};
exports.GroupsIllustration = GroupsIllustration;
//# sourceMappingURL=GroupsIllustration.js.map
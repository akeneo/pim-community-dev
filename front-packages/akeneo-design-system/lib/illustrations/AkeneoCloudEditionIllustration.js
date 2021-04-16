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
exports.AkeneoCloudEditionIllustration = void 0;
var react_1 = __importDefault(require("react"));
var AkeneoCloudEdition_svg_1 = __importDefault(require("../../static/illustrations/AkeneoCloudEdition.svg"));
var theme_1 = require("../theme");
var AkeneoCloudEditionIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: AkeneoCloudEdition_svg_1.default }),
        react_1.default.createElement("g", { fill: "none", fillRule: "evenodd" },
            react_1.default.createElement(theme_1.BrandedPath, { d: "M142.533 113.72c.078.23.146.386.166.438.007.005-.006-.002 0 0 0 0 0 .001 0 0 .001 0 0 0 0 0-.52-5.014 2.323-7.103 3.236-10.433.005-.05.015-.103.03-.163.02-.075.035-.153.046-.23.135-.921-.242-1.993-.957-3.11-.003-.006-.008-.012-.01-.018a10.36 10.36 0 00-.187-.278s-.002-.002-.002-.005a14.074 14.074 0 00-.374-.513l-.04-.052c-.037-.046-.076-.093-.11-.14l-.115-.144-.118-.14c-.04-.05-.08-.097-.122-.147l-.089-.1c-1.123-1.294-2.584-2.573-4.11-3.686 0 0-.013-.015 0 0 .201 1.888.227 3.775.048 5.284-.216 1.819-.866 3.936-1.77 5.855-2.113-.174-4.274-.663-5.959-1.383-1.409-.6-3.022-1.55-4.565-2.676.017.157.035.312.054.468l.018.14.045.334.023.153c.016.112.033.223.051.334l.022.136.072.42.007.042a19.656 19.656 0 00.1.52c.03.144.058.285.09.43l.018.087.09.388a.53.53 0 01.018.076c.038.148.074.297.113.444a20.364 20.364 0 00.411 1.363l.005.016c.251.728.536 1.398.855 1.985.047.085.095.17.143.25.002.003.002.007.005.01h.001c.043.076.088.15.132.22l.006.008c.048.076.097.151.147.222v.001l.021.03v.001c.065.094.131.184.197.27l.034.04c.003.007.008.01.011.017a5.367 5.367 0 00.405.453l.073.069.108.099.083.066c.035.03.071.056.105.084.028.022.056.04.085.06a2.635 2.635 0 00.194.129l.116.064c.028.017.055.03.081.043.045.022.09.04.135.06.022.007.045.02.068.028.07.028.138.052.208.07.047.014.087.03.125.043 3.346.885 6.606-.516 10.693 2.406" }))));
};
exports.AkeneoCloudEditionIllustration = AkeneoCloudEditionIllustration;
//# sourceMappingURL=AkeneoCloudEditionIllustration.js.map
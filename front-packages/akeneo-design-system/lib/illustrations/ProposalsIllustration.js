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
exports.ProposalsIllustration = void 0;
var react_1 = __importDefault(require("react"));
var hooks_1 = require("../hooks");
var Proposals_svg_1 = __importDefault(require("../../static/illustrations/Proposals.svg"));
var ProposalsIllustration = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 256 : _b, props = __rest(_a, ["title", "size"]);
    return (react_1.default.createElement("svg", __assign({ width: size, height: size, viewBox: "0 0 256 256" }, props),
        title && react_1.default.createElement("title", null, title),
        react_1.default.createElement("image", { href: Proposals_svg_1.default }),
        react_1.default.createElement("g", { fill: "none", fillRule: "evenodd" },
            react_1.default.createElement("path", { d: "M135.517 106.069c.14.416.265.7.3.795.012.01-.01-.006 0 0 0 0 0 .002 0 0 .003.002-.001 0-.001 0-.943-9.111 4.222-12.908 5.884-18.96.008-.09.025-.187.053-.297.035-.136.062-.275.082-.415.248-1.674-.44-3.623-1.738-5.652-.006-.013-.015-.023-.02-.033a17.04 17.04 0 00-.336-.506s-.004-.003-.004-.007a27.034 27.034 0 00-.681-.932c-.024-.031-.047-.065-.073-.097-.067-.084-.137-.168-.202-.254l-.207-.262-.214-.255a25.84 25.84 0 00-.221-.265l-.161-.183c-2.041-2.351-4.698-4.675-7.471-6.696 0 0-.021-.031 0 0 .366 3.428.413 6.857.089 9.598-.392 3.306-1.575 7.153-3.216 10.64-3.842-.313-7.767-1.203-10.83-2.51-2.558-1.094-5.491-2.818-8.295-4.864.03.285.065.566.098.85.01.084.023.168.033.254.027.202.052.404.083.607.012.093.026.186.04.28.029.204.06.405.093.606l.04.247a37.942 37.942 0 00.145.838c.05.295.108.588.166.879.002.023.009.043.014.068.053.262.107.519.163.779.01.054.024.105.033.158l.164.707a59.28 59.28 0 00.239.947c.227.856.476 1.682.745 2.474.004.01.008.019.01.028.456 1.324.974 2.542 1.553 3.61.086.154.173.307.26.455.005.006.005.012.009.017h.002c.078.136.159.27.239.397l.003.003.01.014c.087.139.175.275.266.405l.039.053v.004c.118.169.239.332.357.489.022.025.042.05.062.075.005.01.013.017.02.03.139.174.28.343.428.5.1.112.204.218.307.32.044.045.09.085.134.127.066.062.13.12.196.179.05.043.1.08.152.123.06.052.127.102.19.152.05.038.1.073.155.109.066.048.13.092.198.137l.153.094c.07.042.144.08.212.12.05.028.1.053.147.077.083.039.163.074.247.109.038.015.08.036.122.05.125.052.25.094.377.129.086.025.158.05.227.078 6.082 1.607 12.004-.94 19.432 4.372", fill: hooks_1.useTheme().color.brand100 }))));
};
exports.ProposalsIllustration = ProposalsIllustration;
//# sourceMappingURL=ProposalsIllustration.js.map
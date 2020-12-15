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
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.PimView = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var hooks_1 = require("../hooks");
var StyledPimView = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  visibility: ", ";\n  opacity: ", ";\n  transition: opacity 0.5s linear;\n"], ["\n  visibility: ", ";\n  opacity: ", ";\n  transition: opacity 0.5s linear;\n"])), function (_a) {
    var rendered = _a.rendered;
    return (rendered ? 'visible' : 'hidden');
}, function (_a) {
    var rendered = _a.rendered;
    return (rendered ? '1' : '0');
});
var PimView = function (_a) {
    var viewName = _a.viewName, className = _a.className;
    var el = react_1.useRef(null);
    var _b = react_1.useState(null), view = _b[0], setView = _b[1];
    var viewBuilder = hooks_1.useViewBuilder();
    react_1.useEffect(function () {
        if (!viewBuilder) {
            return;
        }
        viewBuilder.build(viewName).then(function (view) {
            view.setElement(el.current).render();
            setView(view);
        });
    }, [viewBuilder, viewName]);
    react_1.useEffect(function () { return function () {
        view && view.remove();
    }; }, [view]);
    return react_1.default.createElement(StyledPimView, { className: className, ref: el, rendered: null !== view });
};
exports.PimView = PimView;
var templateObject_1;
//# sourceMappingURL=PimView.js.map
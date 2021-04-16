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
exports.Overlay = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var hooks_1 = require("../../../../hooks");
var theme_1 = require("../../../../theme");
var OverlayContent = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  background: ", ";\n  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);\n  padding: 10px 0 10px 0;\n  position: absolute;\n  transition: opacity 0.15s ease-in-out;\n  z-index: 2;\n  left: 0;\n  right: 0;\n\n  ", ";\n"], ["\n  background: ", ";\n  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);\n  padding: 10px 0 10px 0;\n  position: absolute;\n  transition: opacity 0.15s ease-in-out;\n  z-index: 2;\n  left: 0;\n  right: 0;\n\n  ",
    ";\n"])), theme_1.getColor('white'), function (_a) {
    var verticalPosition = _a.verticalPosition;
    return 'up' === verticalPosition
        ? styled_components_1.css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n          bottom: 46px;\n        "], ["\n          bottom: 46px;\n        "]))) : styled_components_1.css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n          top: 6px;\n        "], ["\n          top: 6px;\n        "])));
});
var OverlayContainer = styled_components_1.default.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  position: relative;\n"], ["\n  position: relative;\n"])));
var Backdrop = styled_components_1.default.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  position: fixed;\n  top: 0;\n  left: 0;\n  right: 0;\n  bottom: 0;\n  z-index: 1;\n"], ["\n  position: fixed;\n  top: 0;\n  left: 0;\n  right: 0;\n  bottom: 0;\n  z-index: 1;\n"])));
var Overlay = function (_a) {
    var verticalPosition = _a.verticalPosition, onClose = _a.onClose, children = _a.children;
    var overlayRef = react_1.useRef(null);
    verticalPosition = hooks_1.useVerticalPosition(overlayRef, verticalPosition);
    return (react_1.default.createElement(OverlayContainer, null,
        react_1.default.createElement(Backdrop, { "data-testid": "backdrop", onClick: onClose }),
        react_1.default.createElement(OverlayContent, { ref: overlayRef, verticalPosition: verticalPosition }, children)));
};
exports.Overlay = Overlay;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5;
//# sourceMappingURL=Overlay.js.map
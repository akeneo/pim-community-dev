"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
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
exports.useInModal = exports.Modal = void 0;
var react_1 = __importStar(require("react"));
var react_dom_1 = require("react-dom");
var styled_components_1 = __importDefault(require("styled-components"));
var theme_1 = require("../../theme");
var IconButton_1 = require("../IconButton/IconButton");
var icons_1 = require("../../icons");
var hooks_1 = require("../../hooks");
var shared_1 = require("../../shared");
var ModalContext_1 = require("./ModalContext");
Object.defineProperty(exports, "useInModal", { enumerable: true, get: function () { return ModalContext_1.useInModal; } });
var ModalContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  ", "\n  position: fixed;\n  width: 100vw;\n  height: 100vh;\n  top: 0;\n  left: 0;\n  background-color: ", ";\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  justify-content: center;\n  z-index: 2000;\n  overflow: hidden;\n  padding: 20px 80px;\n  box-sizing: border-box;\n"], ["\n  ", "\n  position: fixed;\n  width: 100vw;\n  height: 100vh;\n  top: 0;\n  left: 0;\n  background-color: ", ";\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  justify-content: center;\n  z-index: 2000;\n  overflow: hidden;\n  padding: 20px 80px;\n  box-sizing: border-box;\n"])), theme_1.CommonStyle, theme_1.getColor('white'));
var ModalCloseButton = styled_components_1.default(IconButton_1.IconButton)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  position: fixed;\n  top: 40px;\n  left: 40px;\n"], ["\n  position: fixed;\n  top: 40px;\n  left: 40px;\n"])));
var ModalContent = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: grid;\n  grid-template-columns: 1fr 2fr;\n"], ["\n  display: grid;\n  grid-template-columns: 1fr 2fr;\n"])));
var ModalChildren = styled_components_1.default.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  padding: 20px 40px;\n  min-width: 480px;\n  border-left: 1px solid ", ";\n"], ["\n  display: flex;\n  flex-direction: column;\n  padding: 20px 40px;\n  min-width: 480px;\n  border-left: 1px solid ", ";\n"])), theme_1.getColor('brand', 100));
var IconContainer = styled_components_1.default.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  display: flex;\n  justify-content: flex-end;\n  padding-right: 40px;\n"], ["\n  display: flex;\n  justify-content: flex-end;\n  padding-right: 40px;\n"])));
var SectionTitle = styled_components_1.default.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  height: 20px;\n  color: ", ";\n  font-size: ", ";\n  text-transform: uppercase;\n"], ["\n  height: 20px;\n  color: ", ";\n  font-size: ", ";\n  text-transform: uppercase;\n"])), function (_a) {
    var color = _a.color;
    return theme_1.getColor(color !== null && color !== void 0 ? color : 'grey', 120);
}, function (_a) {
    var size = _a.size;
    return theme_1.getFontSize(size !== null && size !== void 0 ? size : 'default');
});
var Title = styled_components_1.default.div(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n  height: 40px;\n  color: ", ";\n  font-size: ", ";\n  margin-bottom: 10px;\n"], ["\n  display: flex;\n  align-items: center;\n  height: 40px;\n  color: ", ";\n  font-size: ", ";\n  margin-bottom: 10px;\n"])), theme_1.getColor('grey', 140), theme_1.getFontSize('title'));
var BottomButtons = styled_components_1.default.div(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  display: flex;\n  gap: 10px;\n  margin-top: 20px;\n"], ["\n  display: flex;\n  gap: 10px;\n  margin-top: 20px;\n"])));
var TopRightButtons = styled_components_1.default(BottomButtons)(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  position: fixed;\n  top: 40px;\n  right: 40px;\n  margin: 0;\n"], ["\n  position: fixed;\n  top: 40px;\n  right: 40px;\n  margin: 0;\n"])));
var Modal = function (_a) {
    var onClose = _a.onClose, illustration = _a.illustration, closeTitle = _a.closeTitle, children = _a.children, rest = __rest(_a, ["onClose", "illustration", "closeTitle", "children"]);
    var portalNode = document.createElement('div');
    portalNode.setAttribute('id', 'modal-root');
    var containerRef = react_1.useRef(portalNode);
    hooks_1.useShortcut(shared_1.Key.Escape, onClose);
    react_1.useEffect(function () {
        document.body.appendChild(containerRef.current);
        return function () {
            document.body.removeChild(containerRef.current);
        };
    }, []);
    return react_dom_1.createPortal(react_1.default.createElement(ModalContext_1.ModalContext.Provider, { value: true },
        react_1.default.createElement(ModalContainer, __assign({ role: "dialog" }, rest),
            react_1.default.createElement(ModalCloseButton, { title: closeTitle, level: "tertiary", ghost: "borderless", icon: react_1.default.createElement(icons_1.CloseIcon, null), onClick: onClose }),
            undefined === illustration ? (children) : (react_1.default.createElement(ModalContent, null,
                react_1.default.createElement(IconContainer, null, react_1.default.cloneElement(illustration, { size: 220 })),
                react_1.default.createElement(ModalChildren, null, children))))), containerRef.current);
};
exports.Modal = Modal;
Modal.BottomButtons = BottomButtons;
Modal.TopRightButtons = TopRightButtons;
Modal.Title = Title;
Modal.SectionTitle = SectionTitle;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9;
//# sourceMappingURL=Modal.js.map
"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.unmountReactElementRef = exports.mountReactElementRef = void 0;
var react_dom_1 = __importDefault(require("react-dom"));
var mountReactElementRef = function (component, container) {
    react_dom_1.default.render(component, container);
    return container;
};
exports.mountReactElementRef = mountReactElementRef;
var unmountReactElementRef = function (container) {
    react_dom_1.default.unmountComponentAtNode(container);
};
exports.unmountReactElementRef = unmountReactElementRef;
//# sourceMappingURL=reactElementHelper.js.map
"use strict";
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
exports.Skeleton = void 0;
var react_1 = __importStar(require("react"));
var recursiveMap = function (children, callback) {
    return react_1.default.Children.map(children, function (child) {
        if (!react_1.isValidElement(child)) {
            return child;
        }
        return callback(undefined !== child.props.children
            ? react_1.default.cloneElement(child, child.props, recursiveMap(child.props.children, callback))
            : child);
    });
};
var Skeleton = function (_a) {
    var _b = _a.enabled, enabled = _b === void 0 ? false : _b, children = _a.children;
    var skeleton = 'Skeleton';
    return (react_1.default.createElement(react_1.default.Fragment, null, enabled
        ? recursiveMap(children, function (child) {
            if (!('object' === typeof child.type && skeleton in child.type)) {
                return child;
            }
            return react_1.default.createElement(child.type[skeleton], child.props);
        })
        : children));
};
exports.Skeleton = Skeleton;
//# sourceMappingURL=Skeleton.js.map
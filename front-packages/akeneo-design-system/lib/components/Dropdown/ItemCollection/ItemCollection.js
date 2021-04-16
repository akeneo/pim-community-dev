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
exports.ItemCollection = void 0;
var shared_1 = require("../../../shared");
var react_1 = __importStar(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var hooks_1 = require("../../../hooks");
var ItemCollectionContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  max-height: 320px;\n  overflow-y: auto;\n  overflow-x: hidden;\n"], ["\n  max-height: 320px;\n  overflow-y: auto;\n  overflow-x: hidden;\n"])));
var ItemCollection = react_1.default.forwardRef(function (_a, forwardedRef) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    var firstItemRef = react_1.useRef(null);
    var handleKeyDown = react_1.useCallback(function (event) {
        var _a, _b;
        if (null !== event.currentTarget) {
            if (event.key === shared_1.Key.ArrowDown) {
                (_a = event.currentTarget.nextSibling) === null || _a === void 0 ? void 0 : _a.focus();
                event.preventDefault();
            }
            if (event.key === shared_1.Key.ArrowUp) {
                (_b = event.currentTarget.previousSibling) === null || _b === void 0 ? void 0 : _b.focus();
                event.preventDefault();
            }
        }
    }, []);
    var decoratedChildren = react_1.default.Children.map(children, function (child, index) {
        if (react_1.default.isValidElement(child)) {
            return react_1.default.cloneElement(child, {
                ref: 0 === index ? firstItemRef : undefined,
                onKeyDown: handleKeyDown,
            });
        }
        return child;
    });
    hooks_1.useAutoFocus(firstItemRef);
    return (react_1.default.createElement(ItemCollectionContainer, __assign({}, rest, { ref: forwardedRef }), decoratedChildren));
});
exports.ItemCollection = ItemCollection;
var templateObject_1;
//# sourceMappingURL=ItemCollection.js.map
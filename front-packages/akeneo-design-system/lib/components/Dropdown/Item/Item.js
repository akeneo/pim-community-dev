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
Object.defineProperty(exports, "__esModule", { value: true });
exports.ItemLabel = exports.Item = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var theme_1 = require("../../../theme");
var Image_1 = require("../../../components/Image/Image");
var Checkbox_1 = require("../../../components/Checkbox/Checkbox");
var Link_1 = require("../../../components/Link/Link");
var shared_1 = require("../../../shared");
var icons_1 = require("../../../icons");
var ItemLabel = styled_components_1.default.span(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  flex: 1;\n  line-height: 34px;\n"], ["\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  flex: 1;\n  line-height: 34px;\n"])));
exports.ItemLabel = ItemLabel;
var ItemContainer = styled_components_1.default.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  background: ", ";\n  height: ", "px;\n  line-height: ", "px;\n  padding: 0 20px;\n  display: flex;\n  align-items: center;\n  gap: 10px;\n  outline-style: none;\n  cursor: pointer;\n  white-space: nowrap;\n\n  &:focus {\n    box-shadow: inset 0 0 0 2px ", ";\n  }\n\n  ", "\n"], ["\n  background: ", ";\n  height: ", "px;\n  line-height: ", "px;\n  padding: 0 20px;\n  display: flex;\n  align-items: center;\n  gap: 10px;\n  outline-style: none;\n  cursor: pointer;\n  white-space: nowrap;\n\n  &:focus {\n    box-shadow: inset 0 0 0 2px ", ";\n  }\n\n  ",
    "\n"])), theme_1.getColor('white'), function (_a) {
    var tall = _a.tall;
    return (tall ? 44 : 34);
}, function (_a) {
    var tall = _a.tall;
    return (tall ? 44 : 34);
}, theme_1.getColor('blue', 40), function (_a) {
    var disabled = _a.disabled;
    return disabled
        ? styled_components_1.css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n          cursor: not-allowed;\n          color: ", ";\n        "], ["\n          cursor: not-allowed;\n          color: ", ";\n        "])), theme_1.getColor('grey', 100)) : styled_components_1.css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n          cursor: pointer;\n          color: ", ";\n          a {\n            color: ", ";\n          }\n\n          &:hover a,\n          &:hover {\n            background: ", ";\n            color: ", ";\n          }\n          &:active a,\n          &:active {\n            color: ", ";\n            font-weight: 700;\n          }\n        "], ["\n          cursor: pointer;\n          color: ", ";\n          a {\n            color: ", ";\n          }\n\n          &:hover a,\n          &:hover {\n            background: ", ";\n            color: ", ";\n          }\n          &:active a,\n          &:active {\n            color: ", ";\n            font-weight: 700;\n          }\n        "])), theme_1.getColor('grey', 120), theme_1.getColor('grey', 120), theme_1.getColor('grey', 20), theme_1.getColor('brand', 140), theme_1.getColor('brand', 100));
});
var Item = react_1.default.forwardRef(function (_a, forwardedRef) {
    var children = _a.children, onKeyDown = _a.onKeyDown, _b = _a.disabled, disabled = _b === void 0 ? false : _b, onClick = _a.onClick, title = _a.title, rest = __rest(_a, ["children", "onKeyDown", "disabled", "onClick", "title"]);
    var tall = false;
    var actionableRef = react_1.useRef(null);
    var handleClick = react_1.useCallback(function (event) {
        if (disabled)
            return;
        if (null !== actionableRef.current && actionableRef.current !== event.target) {
            actionableRef.current.click();
        }
        else if (undefined !== onClick) {
            onClick(event);
        }
    }, [disabled]);
    var handleKeyDown = react_1.useCallback(function (event) {
        if (shared_1.Key.Enter === event.key || shared_1.Key.Space === event.key) {
            event.preventDefault();
            handleClick(event);
            return;
        }
        onKeyDown && onKeyDown(event);
    }, [onKeyDown, handleClick]);
    var decoratedChildren = react_1.default.Children.map(children, function (child) {
        if (typeof child === 'string') {
            return (react_1.default.createElement(react_1.default.Fragment, null,
                react_1.default.createElement(ItemLabel, { title: title !== null && title !== void 0 ? title : child }, child),
                disabled && react_1.default.createElement(icons_1.LockIcon, { size: 18 })));
        }
        if (react_1.isValidElement(child) && child.type === Image_1.Image) {
            tall = true;
            return react_1.default.cloneElement(child, {
                width: 34,
                height: 34,
            });
        }
        if (react_1.isValidElement(child) && child.type === Link_1.Link) {
            return (react_1.default.createElement(react_1.default.Fragment, null,
                react_1.default.createElement(ItemLabel, null, react_1.default.cloneElement(child, {
                    ref: actionableRef,
                    decorated: false,
                    disabled: disabled,
                    tabIndex: -1,
                })),
                disabled && react_1.default.createElement(icons_1.LockIcon, { size: 18 })));
        }
        if (react_1.isValidElement(child) && child.type === Checkbox_1.Checkbox) {
            return react_1.default.cloneElement(child, {
                ref: actionableRef,
                readOnly: disabled,
                tabIndex: -1,
            });
        }
        return child;
    });
    return (react_1.default.createElement(ItemContainer, __assign({ tall: tall, tabIndex: null === actionableRef.current && !disabled ? 0 : -1, onClick: handleClick, onKeyDown: handleKeyDown, disabled: disabled, "aria-disabled": disabled }, rest, { ref: forwardedRef }), decoratedChildren));
});
exports.Item = Item;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=Item.js.map
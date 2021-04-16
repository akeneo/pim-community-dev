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
exports.Helper = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var icons_1 = require("../../icons");
var theme_1 = require("../../theme");
var getBackgroundColor = function (level) {
    switch (level) {
        case 'info':
            return theme_1.getColor('blue', 10);
        case 'warning':
            return theme_1.getColor('yellow', 10);
        case 'error':
            return theme_1.getColor('red', 10);
        case 'success':
            return theme_1.getColor('green', 10);
    }
};
var getFontColor = function (level, inline) {
    switch (level) {
        case 'info':
            return theme_1.getColor('grey', 120);
        case 'warning':
            return theme_1.getColor(inline ? 'grey' : 'yellow', 120);
        case 'error':
            return theme_1.getColor('red', inline ? 100 : 120);
        case 'success':
            return theme_1.getColor(inline ? 'grey' : 'green', 120);
    }
};
var getIconColor = function (level, inline) {
    switch (level) {
        case 'info':
            return theme_1.getColor('blue', 100);
        case 'warning':
            return theme_1.getColor('yellow', inline ? 100 : 120);
        case 'error':
            return theme_1.getColor('red', inline ? 100 : 120);
        case 'success':
            return theme_1.getColor('green', inline ? 100 : 120);
    }
};
var getIcon = function (level) {
    switch (level) {
        case 'info':
            return react_1.default.createElement(icons_1.InfoRoundIcon, null);
        case 'warning':
            return react_1.default.createElement(icons_1.DangerIcon, null);
        case 'error':
            return react_1.default.createElement(icons_1.DangerIcon, null);
        case 'success':
            return react_1.default.createElement(icons_1.CheckRoundIcon, null);
    }
};
var getSeparatorColor = function (level) {
    switch (level) {
        case 'info':
            return theme_1.getColor('grey', 80);
        case 'warning':
            return theme_1.getColor('yellow', 120);
        case 'error':
            return theme_1.getColor('red', 120);
        case 'success':
            return theme_1.getColor('green', 120);
    }
};
var getLinkColor = function (level, inline) {
    switch (level) {
        case 'info':
            return theme_1.getColor('blue', 100);
        case 'warning':
            return theme_1.getColor('yellow', 120);
        case 'error':
            return theme_1.getColor('red', inline ? 100 : 120);
        case 'success':
            return theme_1.getColor('green', inline ? 100 : 120);
    }
};
var Container = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  font-weight: 400;\n  padding-right: 20px;\n  color: ", ";\n\n  ", "\n"], ["\n  display: flex;\n  font-weight: 400;\n  padding-right: 20px;\n  color: ", ";\n\n  ",
    "\n"])), function (props) { return getFontColor(props.level, props.inline); }, function (props) {
    return !props.inline && styled_components_1.css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      min-height: 44px;\n      background-color: ", ";\n    "], ["\n      min-height: 44px;\n      background-color: ", ";\n    "])), getBackgroundColor(props.level));
});
var IconContainer = styled_components_1.default.span(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  height: ", ";\n  margin: ", ";\n  color: ", ";\n"], ["\n  height: ", ";\n  margin: ", ";\n  color: ", ";\n"])), function (_a) {
    var inline = _a.inline;
    return (inline ? '16px' : '20px');
}, function (_a) {
    var inline = _a.inline;
    return (inline ? '2px 0' : '12px 10px');
}, function (props) { return getIconColor(props.level, props.inline); });
var TextContainer = styled_components_1.default.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  padding-left: ", ";\n  white-space: break-spaces;\n  flex: 1;\n\n  a {\n    color: ", ";\n  }\n\n  ", "\n"], ["\n  padding-left: ", ";\n  white-space: break-spaces;\n  flex: 1;\n\n  a {\n    color: ", ";\n  }\n\n  ",
    "\n"])), function (_a) {
    var inline = _a.inline;
    return (inline ? '4px' : '10px');
}, function (_a) {
    var level = _a.level, inline = _a.inline;
    return getLinkColor(level, inline);
}, function (_a) {
    var inline = _a.inline, level = _a.level;
    return !inline && styled_components_1.css(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n      margin: 12px 0;\n      border-left: 1px solid ", ";\n    "], ["\n      margin: 12px 0;\n      border-left: 1px solid ", ";\n    "])), getSeparatorColor(level));
});
var Helper = react_1.default.forwardRef(function (_a, forwardedRef) {
    var _b = _a.level, level = _b === void 0 ? 'info' : _b, _c = _a.inline, inline = _c === void 0 ? false : _c, icon = _a.icon, children = _a.children, rest = __rest(_a, ["level", "inline", "icon", "children"]);
    return (react_1.default.createElement(Container, __assign({ ref: forwardedRef, level: level, inline: inline }, rest),
        react_1.default.createElement(IconContainer, { inline: inline, level: level }, react_1.default.cloneElement(undefined === icon ? getIcon(level) : icon, { size: inline ? 16 : 20 })),
        react_1.default.createElement(TextContainer, { level: level, inline: inline }, children)));
});
exports.Helper = Helper;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5;
//# sourceMappingURL=Helper.js.map
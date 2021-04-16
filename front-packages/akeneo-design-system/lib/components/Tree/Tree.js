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
exports.Tree = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var theme_1 = require("../../theme");
var Checkbox_1 = require("../Checkbox/Checkbox");
var icons_1 = require("../../icons");
var folderIconCss = styled_components_1.css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  vertical-align: middle;\n  transition: color 0.2s ease;\n  margin-right: 5px;\n"], ["\n  vertical-align: middle;\n  transition: color 0.2s ease;\n  margin-right: 5px;\n"])));
var TreeContainer = styled_components_1.default.li(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: block;\n  color: ", ";\n"], ["\n  display: block;\n  color: ", ";\n"])), theme_1.getColor('grey140'));
var SubTreesContainer = styled_components_1.default.ul(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  margin: 0 0 0 20px;\n  padding: 0;\n"], ["\n  margin: 0 0 0 20px;\n  padding: 0;\n"])));
var TreeArrowIcon = styled_components_1.default(icons_1.ArrowRightIcon)(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  transform: rotate(", "deg);\n  transition: transform 0.2s ease-out;\n  vertical-align: middle;\n  color: ", ";\n  cursor: pointer;\n"], ["\n  transform: rotate(", "deg);\n  transition: transform 0.2s ease-out;\n  vertical-align: middle;\n  color: ", ";\n  cursor: pointer;\n"])), function (_a) {
    var $isFolderOpen = _a.$isFolderOpen;
    return ($isFolderOpen ? '90' : '0');
}, theme_1.getColor('grey100'));
var TreeLeafNotSelectedIcon = styled_components_1.default(icons_1.FolderIcon)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  ", "\n"], ["\n  ", "\n"])), folderIconCss);
var TreeFolderSelectedIcon = styled_components_1.default(icons_1.FoldersPlainIcon)(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  ", "\n  color: ", ";\n"], ["\n  ", "\n  color: ", ";\n"])), folderIconCss, theme_1.getColor('blue100'));
var TreeLeafSelectedIcon = styled_components_1.default(icons_1.FolderPlainIcon)(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n  ", "\n  color: ", ";\n"], ["\n  ", "\n  color: ", ";\n"])), folderIconCss, theme_1.getColor('blue100'));
var TreeFolderNotSelectedIcon = styled_components_1.default(icons_1.FoldersIcon)(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  ", "\n"], ["\n  ", "\n"])), folderIconCss);
var TreeLoaderIcon = styled_components_1.default(icons_1.LoaderIcon)(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  ", "\n  color: ", ";\n"], ["\n  ", "\n  color: ", ";\n"])), folderIconCss, theme_1.getColor('grey100'));
var TreeLine = styled_components_1.default.div(templateObject_11 || (templateObject_11 = __makeTemplateObject(["\n  height: 40px;\n  line-height: 40px;\n  overflow: hidden;\n  width: 100%;\n  ", "\n"], ["\n  height: 40px;\n  line-height: 40px;\n  overflow: hidden;\n  width: 100%;\n  ",
    "\n"])), function (_a) {
    var $selected = _a.$selected;
    return $selected && styled_components_1.css(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n      color: ", ";\n    "], ["\n      color: ", ";\n    "])), theme_1.getColor('blue100'));
});
var NodeCheckbox = styled_components_1.default(Checkbox_1.Checkbox)(templateObject_12 || (templateObject_12 = __makeTemplateObject(["\n  display: inline-block;\n  vertical-align: middle;\n  margin-right: 8px;\n"], ["\n  display: inline-block;\n  vertical-align: middle;\n  margin-right: 8px;\n"])));
var ArrowButton = styled_components_1.default.button(templateObject_13 || (templateObject_13 = __makeTemplateObject(["\n  height: 30px;\n  width: 30px;\n  vertical-align: middle;\n  margin-right: 2px;\n  padding: 0;\n  border: none;\n  background: none;\n  &:not(:disabled) {\n    cursor: pointer;\n  }\n"], ["\n  height: 30px;\n  width: 30px;\n  vertical-align: middle;\n  margin-right: 2px;\n  padding: 0;\n  border: none;\n  background: none;\n  &:not(:disabled) {\n    cursor: pointer;\n  }\n"])));
var LabelWithFolder = styled_components_1.default.button(templateObject_16 || (templateObject_16 = __makeTemplateObject(["\n  ", "\n  height: 30px;\n  vertical-align: middle;\n  background: none;\n  border: none;\n  cursor: pointer;\n  padding: 0 5px 0 0;\n  cursor: pointer;\n  text-overflow: ellipsis;\n  overflow: hidden;\n  max-width: calc(100% - 35px);\n  text-align: left;\n  white-space: nowrap;\n  ", "\n  &:hover {\n    ", "\n  }\n"], ["\n  ", "\n  height: 30px;\n  vertical-align: middle;\n  background: none;\n  border: none;\n  cursor: pointer;\n  padding: 0 5px 0 0;\n  cursor: pointer;\n  text-overflow: ellipsis;\n  overflow: hidden;\n  max-width: calc(100% - 35px);\n  text-align: left;\n  white-space: nowrap;\n  ",
    "\n  &:hover {\n    ",
    "\n  }\n"])), theme_1.CommonStyle, function (_a) {
    var $selected = _a.$selected;
    return $selected && styled_components_1.css(templateObject_14 || (templateObject_14 = __makeTemplateObject(["\n      color: ", ";\n    "], ["\n      color: ", ";\n    "])), theme_1.getColor('blue100'));
}, function (_a) {
    var $selected = _a.$selected;
    return !$selected && styled_components_1.css(templateObject_15 || (templateObject_15 = __makeTemplateObject(["\n        color: ", ";\n      "], ["\n        color: ", ";\n      "])), theme_1.getColor('grey140'));
});
var TreeIcon = function (_a) {
    var isLoading = _a.isLoading, isLeaf = _a.isLeaf, selected = _a.selected;
    if (isLoading) {
        return react_1.default.createElement(TreeLoaderIcon, { size: 24 });
    }
    if (isLeaf) {
        return selected ? react_1.default.createElement(TreeLeafSelectedIcon, { size: 24 }) : react_1.default.createElement(TreeLeafNotSelectedIcon, { size: 24 });
    }
    return selected ? react_1.default.createElement(TreeFolderSelectedIcon, { size: 24 }) : react_1.default.createElement(TreeFolderNotSelectedIcon, { size: 24 });
};
var Tree = function (_a) {
    var label = _a.label, value = _a.value, children = _a.children, _b = _a.isLeaf, isLeaf = _b === void 0 ? false : _b, _c = _a.selected, selected = _c === void 0 ? false : _c, _d = _a.isLoading, isLoading = _d === void 0 ? false : _d, _e = _a.selectable, selectable = _e === void 0 ? false : _e, _f = _a.readOnly, readOnly = _f === void 0 ? false : _f, onChange = _a.onChange, onOpen = _a.onOpen, onClose = _a.onClose, onClick = _a.onClick, _g = _a._isRoot, _isRoot = _g === void 0 ? true : _g, rest = __rest(_a, ["label", "value", "children", "isLeaf", "selected", "isLoading", "selectable", "readOnly", "onChange", "onOpen", "onClose", "onClick", "_isRoot"]);
    var subTrees = [];
    react_1.default.Children.forEach(children, function (child) {
        if (!react_1.isValidElement(child)) {
            throw new Error('Tree component only accepts Tree as children');
        }
        subTrees.push(child);
    });
    var _h = react_1.default.useState(subTrees.length > 0), isOpen = _h[0], setOpen = _h[1];
    var handleOpen = react_1.default.useCallback(function () {
        setOpen(true);
        if (onOpen) {
            onOpen(value);
        }
    }, [onOpen, value]);
    var handleClose = react_1.default.useCallback(function () {
        setOpen(false);
        if (onClose) {
            onClose(value);
        }
    }, [onClose, value]);
    var handleArrowClick = react_1.default.useCallback(function () {
        if (isLeaf) {
            return;
        }
        isOpen ? handleClose() : handleOpen();
    }, [isOpen, handleClose, handleOpen, isLeaf]);
    var handleClick = react_1.default.useCallback(function () {
        if (onClick) {
            onClick(value);
        }
        else {
            handleArrowClick();
        }
    }, [handleArrowClick, onClick, value]);
    var handleSelect = react_1.default.useCallback(function (checked, event) {
        if (onChange) {
            onChange(value, checked, event);
        }
    }, [onChange, value]);
    var result = (react_1.default.createElement(TreeContainer, __assign({ role: "treeitem", "aria-expanded": isOpen }, rest),
        react_1.default.createElement(TreeLine, { "$selected": selected },
            react_1.default.createElement(ArrowButton, { disabled: isLeaf, role: "button", onClick: handleArrowClick }, !isLeaf && react_1.default.createElement(TreeArrowIcon, { "$isFolderOpen": isOpen, size: 14 })),
            selectable && react_1.default.createElement(NodeCheckbox, { checked: selected, onChange: handleSelect, readOnly: readOnly }),
            react_1.default.createElement(LabelWithFolder, { onClick: handleClick, "$selected": selected, title: label, "aria-selected": selected },
                react_1.default.createElement(TreeIcon, { isLoading: isLoading, isLeaf: isLeaf, selected: selected }),
                label)),
        isOpen && !isLeaf && subTrees.length > 0 && (react_1.default.createElement(SubTreesContainer, { role: "group" }, subTrees.map(function (subTree) {
            return react_1.default.cloneElement(subTree, {
                key: JSON.stringify(subTree.props.value),
                _isRoot: false,
            });
        })))));
    return _isRoot ? react_1.default.createElement("ul", { role: "tree" }, result) : result;
};
exports.Tree = Tree;
Tree.displayName = 'Tree';
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10, templateObject_11, templateObject_12, templateObject_13, templateObject_14, templateObject_15, templateObject_16;
//# sourceMappingURL=Tree.js.map
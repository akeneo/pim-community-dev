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
exports.List = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var theme_1 = require("../../theme");
var Button_1 = require("../Button/Button");
var IconButton_1 = require("../IconButton/IconButton");
var ListContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n"], ["\n  display: flex;\n  flex-direction: column;\n"])));
var CellContainer = styled_components_1.default.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  min-height: 54px;\n  padding: 17px 0;\n  box-sizing: border-box;\n  font-size: ", ";\n  color: ", ";\n  display: flex;\n\n  ", ";\n"], ["\n  min-height: 54px;\n  padding: 17px 0;\n  box-sizing: border-box;\n  font-size: ", ";\n  color: ", ";\n  display: flex;\n\n  ",
    ";\n"])), theme_1.getFontSize('default'), theme_1.getColor('grey', 140), function (_a) {
    var width = _a.width;
    return 'auto' === width
        ? styled_components_1.css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n          flex: 1;\n        "], ["\n          flex: 1;\n        "]))) : styled_components_1.css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n          width: ", "px;\n        "], ["\n          width: ", "px;\n        "])), width);
});
var TitleCell = styled_components_1.default(CellContainer)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  color: ", ";\n  font-style: italic;\n  overflow: hidden;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n"], ["\n  color: ", ";\n  font-style: italic;\n  overflow: hidden;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n"])), theme_1.getColor('purple', 100));
var ActionCellContainer = styled_components_1.default(CellContainer)(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  opacity: 0;\n  display: flex;\n  gap: 10px;\n"], ["\n  opacity: 0;\n  display: flex;\n  gap: 10px;\n"])));
var RemoveCellContainer = styled_components_1.default(CellContainer)(templateObject_7 || (templateObject_7 = __makeTemplateObject([""], [""])));
var RemoveCell = function (_a) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    return (react_1.default.createElement(RemoveCellContainer, __assign({ width: "auto" }, rest), children));
};
var RowActionContainer = styled_components_1.default.div(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  display: flex;\n  margin-left: 30px;\n  gap: 10px;\n"], ["\n  display: flex;\n  margin-left: 30px;\n  gap: 10px;\n"])));
var RowContainer = styled_components_1.default.div(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  outline-style: none;\n  padding: 0 10px;\n  border-bottom: 1px solid ", ";\n\n  &:hover {\n    background-color: ", ";\n  }\n\n  &:focus {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  &:hover ", " {\n    opacity: 1;\n  }\n\n  ", " {\n    align-items: ", ";\n  }\n\n  ", ", ", " {\n    height: ", ";\n    align-items: center;\n  }\n"], ["\n  display: flex;\n  flex-direction: column;\n  outline-style: none;\n  padding: 0 10px;\n  border-bottom: 1px solid ", ";\n\n  &:hover {\n    background-color: ", ";\n  }\n\n  &:focus {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  &:hover ", " {\n    opacity: 1;\n  }\n\n  ", " {\n    align-items: ", ";\n  }\n\n  ", ", ", " {\n    height: ", ";\n    align-items: center;\n  }\n"])), theme_1.getColor('grey', 60), theme_1.getColor('grey', 20), theme_1.getColor('blue', 40), ActionCellContainer, CellContainer, function (_a) {
    var multiline = _a.multiline;
    return (multiline ? 'start' : 'center');
}, TitleCell, RemoveCellContainer, function (_a) {
    var multiline = _a.multiline;
    return (multiline ? '74px' : 'auto');
});
var RowContentContainer = styled_components_1.default.div(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n  display: flex;\n"], ["\n  display: flex;\n"])));
var RowDataContainer = styled_components_1.default.div(templateObject_11 || (templateObject_11 = __makeTemplateObject(["\n  display: flex;\n  gap: 10px;\n  flex: 1;\n  min-width: 0;\n"], ["\n  display: flex;\n  gap: 10px;\n  flex: 1;\n  min-width: 0;\n"])));
var RowHelpers = styled_components_1.default.div(templateObject_12 || (templateObject_12 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  gap: 4px;\n  margin-bottom: 10px;\n"], ["\n  display: flex;\n  flex-direction: column;\n  gap: 4px;\n  margin-bottom: 10px;\n"])));
var Row = function (_a) {
    var children = _a.children, _b = _a.multiline, multiline = _b === void 0 ? false : _b;
    var actionCellChild = [];
    var cells = [];
    var helpers = [];
    react_1.default.Children.forEach(children, function (child) {
        if (react_1.isValidElement(child) && (child.type === RemoveCell || child.type === ActionCell)) {
            actionCellChild.push(child);
        }
        else if (react_1.isValidElement(child) && child.type === RowHelpers) {
            helpers.push(child);
        }
        else {
            cells.push(child);
        }
    });
    return (react_1.default.createElement(RowContainer, { multiline: multiline, tabIndex: 0 },
        react_1.default.createElement(RowContentContainer, null,
            react_1.default.createElement(RowDataContainer, null, cells),
            actionCellChild.length > 0 && react_1.default.createElement(RowActionContainer, null, actionCellChild)),
        helpers));
};
var Cell = function (_a) {
    var title = _a.title, width = _a.width, children = _a.children, rest = __rest(_a, ["title", "width", "children"]);
    title = undefined === title && typeof children === 'string' ? children : title;
    return (react_1.default.createElement(CellContainer, __assign({ width: width, title: title }, rest), children));
};
var ActionCell = function (_a) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    var decoratedChildren = react_1.default.Children.map(children, function (child) {
        if (react_1.default.isValidElement(child) && (child.type === Button_1.Button || child.type === IconButton_1.IconButton)) {
            return react_1.default.cloneElement(child, {
                size: 'small',
                ghost: true,
                level: 'tertiary',
            });
        }
        return child;
    });
    return react_1.default.createElement(ActionCellContainer, __assign({}, rest), decoratedChildren);
};
var List = function (_a) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    return react_1.default.createElement(ListContainer, __assign({}, rest), children);
};
exports.List = List;
Row.displayName = 'List.Row';
Cell.displayName = 'List.Cell';
TitleCell.displayName = 'List.TitleCell';
ActionCell.displayName = 'List.ActionCell';
RemoveCell.displayName = 'List.RemoveCell';
List.Row = Row;
List.Cell = Cell;
List.TitleCell = TitleCell;
List.ActionCell = ActionCell;
List.RemoveCell = RemoveCell;
List.RowHelpers = RowHelpers;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10, templateObject_11, templateObject_12;
//# sourceMappingURL=List.js.map
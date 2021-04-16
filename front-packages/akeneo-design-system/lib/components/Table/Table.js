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
exports.Table = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var TableCell_1 = require("./TableCell/TableCell");
var TableHeader_1 = require("./TableHeader/TableHeader");
var TableHeaderCell_1 = require("./TableHeaderCell/TableHeaderCell");
var TableActionCell_1 = require("./TableActionCell/TableActionCell");
var TableRow_1 = require("./TableRow/TableRow");
var SelectableContext_1 = require("./SelectableContext");
var TableBody_1 = require("./TableBody/TableBody");
var TableContainer = styled_components_1.default.table(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  border-collapse: collapse;\n  width: 100%;\n"], ["\n  border-collapse: collapse;\n  width: 100%;\n"])));
var Table = function (_a) {
    var _b = _a.isSelectable, isSelectable = _b === void 0 ? false : _b, _c = _a.displayCheckbox, displayCheckbox = _c === void 0 ? false : _c, children = _a.children, rest = __rest(_a, ["isSelectable", "displayCheckbox", "children"]);
    return (react_1.default.createElement(SelectableContext_1.SelectableContext.Provider, { value: { isSelectable: isSelectable, displayCheckbox: displayCheckbox } },
        react_1.default.createElement(TableContainer, __assign({}, rest), children)));
};
exports.Table = Table;
TableHeader_1.TableHeader.displayName = 'Table.Header';
TableHeaderCell_1.TableHeaderCell.displayName = 'Table.HeaderCell';
TableBody_1.TableBody.displayName = 'Table.Body';
TableRow_1.TableRow.displayName = 'Table.Row';
TableCell_1.TableCell.displayName = 'Table.Cell';
TableActionCell_1.TableActionCell.displayName = 'Table.ActionCell';
Table.Header = TableHeader_1.TableHeader;
Table.HeaderCell = TableHeaderCell_1.TableHeaderCell;
Table.Body = TableBody_1.TableBody;
Table.Row = TableRow_1.TableRow;
Table.Cell = TableCell_1.TableCell;
Table.ActionCell = TableActionCell_1.TableActionCell;
var templateObject_1;
//# sourceMappingURL=Table.js.map
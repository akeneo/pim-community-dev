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
exports.TableHeaderCell = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var theme_1 = require("../../../theme");
var icons_1 = require("../../../icons");
var HeaderCellContainer = styled_components_1.default.th(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  background: linear-gradient(to top, ", " 1px, ", " 0px);\n  height: 44px;\n  text-align: left;\n  color: ", ";\n  font-weight: normal;\n  box-sizing: content-box;\n\n  ", ";\n"], ["\n  background: linear-gradient(to top, ", " 1px, ", " 0px);\n  height: 44px;\n  text-align: left;\n  color: ", ";\n  font-weight: normal;\n  box-sizing: content-box;\n\n  ",
    ";\n"])), theme_1.getColor('grey', 120), theme_1.getColor('white'), function (_a) {
    var isSorted = _a.isSorted;
    return theme_1.getColor(isSorted ? 'brand' : 'grey', 100);
}, function (_a) {
    var isSortable = _a.isSortable;
    return isSortable && styled_components_1.css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      cursor: pointer;\n    "], ["\n      cursor: pointer;\n    "])));
});
var HeaderCellContentContainer = styled_components_1.default.span(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  color: ", ";\n  padding: 0 10px;\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  + svg {\n    vertical-align: middle;\n  }\n"], ["\n  color: ", ";\n  padding: 0 10px;\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  + svg {\n    vertical-align: middle;\n  }\n"])), theme_1.getColor('grey', 140));
var TableHeaderCell = react_1.default.forwardRef(function (_a, forwardedRef) {
    var _b = _a.isSortable, isSortable = _b === void 0 ? false : _b, onDirectionChange = _a.onDirectionChange, sortDirection = _a.sortDirection, children = _a.children, rest = __rest(_a, ["isSortable", "onDirectionChange", "sortDirection", "children"]);
    if (isSortable && (onDirectionChange === undefined || sortDirection === undefined)) {
        throw Error('Sortable header should provide onDirectionChange and direction props');
    }
    if (!isSortable && (onDirectionChange !== undefined || sortDirection !== undefined)) {
        throw Error('Not sortable header does not provide onDirectionChange and direction props');
    }
    var handleClick = function () {
        switch (sortDirection) {
            case 'ascending':
                onDirectionChange && onDirectionChange('descending');
                break;
            case 'descending':
            case 'none':
                onDirectionChange && onDirectionChange('ascending');
                break;
        }
    };
    return (react_1.default.createElement(HeaderCellContainer, __assign({ isSorted: sortDirection !== 'none', isSortable: isSortable, "aria-sort": sortDirection, onClick: handleClick }, rest),
        react_1.default.createElement(HeaderCellContentContainer, { ref: forwardedRef }, children),
        isSortable &&
            (sortDirection === 'descending' || sortDirection === 'none' ? (react_1.default.createElement(icons_1.ArrowDownIcon, { size: 14 })) : (react_1.default.createElement(icons_1.ArrowUpIcon, { size: 14 })))));
});
exports.TableHeaderCell = TableHeaderCell;
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=TableHeaderCell.js.map
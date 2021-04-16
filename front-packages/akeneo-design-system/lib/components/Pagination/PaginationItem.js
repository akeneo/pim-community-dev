"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
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
Object.defineProperty(exports, "__esModule", { value: true });
exports.PAGINATION_SEPARATOR = exports.PaginationItem = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var theme_1 = require("../../theme");
var PAGINATION_SEPARATOR = '…';
exports.PAGINATION_SEPARATOR = PAGINATION_SEPARATOR;
var PaginationItem = function (_a) {
    var currentPage = _a.currentPage, page = _a.page, followPage = _a.followPage;
    var handleClick = react_1.useCallback(function () {
        if (page !== PAGINATION_SEPARATOR) {
            followPage(parseInt(page));
        }
    }, [page, followPage]);
    return (react_1.default.createElement(PaginationItemContainer, { onClick: handleClick, "data-testid": "paginationItem", title: page !== PAGINATION_SEPARATOR ? "No. " + page : '', disabled: page === PAGINATION_SEPARATOR, currentPage: currentPage, page: page, type: "button" }, page));
};
exports.PaginationItem = PaginationItem;
var currentPaginationItemMixin = styled_components_1.css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  border: 1px ", " solid;\n  color: ", ";\n"], ["\n  border: 1px ", " solid;\n  color: ", ";\n"])), theme_1.getColor('brand', 100), theme_1.getColor('brand', 100));
var otherPaginationItemMixin = styled_components_1.css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  border: 1px ", " solid;\n  color: ", ";\n"], ["\n  border: 1px ", " solid;\n  color: ", ";\n"])), theme_1.getColor('grey', 80), theme_1.getColor('grey', 100));
var disabledMixin = styled_components_1.css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  cursor: default;\n  :hover {\n    background-color: ", ";\n  }\n"], ["\n  cursor: default;\n  :hover {\n    background-color: ", ";\n  }\n"])), theme_1.getColor('white'));
var PaginationItemContainer = styled_components_1.default.button(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  ", "\n  display: inline-block;\n  border-width: 1px;\n  font-size: 13px;\n  font-weight: 400;\n  text-transform: uppercase;\n  border-radius: 16px;\n  padding: 0 10px;\n  height: 22px;\n  line-height: 21px;\n  cursor: pointer;\n  font-family: inherit;\n  transition: background-color 0.1s ease 0s;\n  min-width: 40px;\n  text-align: center;\n  box-sizing: border-box;\n  background-color: ", ";\n\n  :hover {\n    background-color: ", ";\n  }\n\n  :focus {\n    outline: 0;\n  }\n\n  ", "\n"], ["\n  ", "\n  display: inline-block;\n  border-width: 1px;\n  font-size: 13px;\n  font-weight: 400;\n  text-transform: uppercase;\n  border-radius: 16px;\n  padding: 0 10px;\n  height: 22px;\n  line-height: 21px;\n  cursor: pointer;\n  font-family: inherit;\n  transition: background-color 0.1s ease 0s;\n  min-width: 40px;\n  text-align: center;\n  box-sizing: border-box;\n  background-color: ", ";\n\n  :hover {\n    background-color: ", ";\n  }\n\n  :focus {\n    outline: 0;\n  }\n\n  ", "\n"])), function (_a) {
    var currentPage = _a.currentPage;
    return (currentPage ? currentPaginationItemMixin : otherPaginationItemMixin);
}, theme_1.getColor('white'), theme_1.getColor('grey', 20), function (_a) {
    var disabled = _a.disabled;
    return (disabled ? disabledMixin : null);
});
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=PaginationItem.js.map
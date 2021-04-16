"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Pagination = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var PaginationItem_1 = require("./PaginationItem");
var MAX_PAGINATION_ITEMS_WITHOUT_SEPARATOR = 4;
var Pagination = function (_a) {
    var currentPage = _a.currentPage, totalItems = _a.totalItems, _b = _a.itemsPerPage, itemsPerPage = _b === void 0 ? 25 : _b, followPage = _a.followPage;
    if (itemsPerPage <= 0) {
        throw new Error('Number of items per page cannot be lower or equal than 0');
    }
    var numberOfPages = Math.ceil(totalItems / itemsPerPage);
    if (currentPage > numberOfPages) {
        throw new Error('The current page cannot be greater than the total number of pages');
    }
    if (numberOfPages <= 1) {
        return null;
    }
    var pages = computePages(currentPage, numberOfPages);
    return (react_1.default.createElement(PaginationContainer, null, pages.map(function (page, index) {
        return (react_1.default.createElement(PaginationItem_1.PaginationItem, { currentPage: page === currentPage, key: index, followPage: followPage, page: page }));
    })));
};
exports.Pagination = Pagination;
var PaginationContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  height: 44px;\n  margin: 10px 0 10px 0;\n  align-items: center;\n  display: flex;\n  justify-content: center;\n  gap: 10px;\n"], ["\n  height: 44px;\n  margin: 10px 0 10px 0;\n  align-items: center;\n  display: flex;\n  justify-content: center;\n  gap: 10px;\n"])));
function computePages(currentPage, numberOfPages) {
    if (numberOfPages <= MAX_PAGINATION_ITEMS_WITHOUT_SEPARATOR) {
        return Array.from(Array(numberOfPages).keys()).map(function (page) { return page + 1; });
    }
    var FIRST_PAGE = 1;
    var SECOND_PAGE = 2;
    var THIRD_PAGE = 3;
    var FOURTH_PAGE = 4;
    var LAST_PAGE = numberOfPages;
    var SECOND_LAST = LAST_PAGE - 1;
    var THIRD_LAST = LAST_PAGE - 2;
    var FOURTH_LAST = LAST_PAGE - 3;
    var PREVIOUS_PAGE = currentPage - 1;
    var NEXT_PAGE = currentPage + 1;
    var pages = [FIRST_PAGE];
    if (currentPage >= FOURTH_PAGE) {
        pages.push(PaginationItem_1.PAGINATION_SEPARATOR);
    }
    if (currentPage > SECOND_PAGE) {
        if (currentPage === LAST_PAGE) {
            pages.push(THIRD_LAST);
        }
        pages.push(PREVIOUS_PAGE);
    }
    if (currentPage !== FIRST_PAGE && currentPage !== LAST_PAGE) {
        pages.push(currentPage);
    }
    if (currentPage < SECOND_LAST) {
        pages.push(NEXT_PAGE);
    }
    if (currentPage === FIRST_PAGE) {
        pages.push(THIRD_PAGE);
    }
    if (currentPage <= FOURTH_LAST) {
        pages.push(PaginationItem_1.PAGINATION_SEPARATOR);
    }
    pages.push(LAST_PAGE);
    return pages;
}
var templateObject_1;
//# sourceMappingURL=Pagination.js.map
"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Search = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var theme_1 = require("../../theme");
var icons_1 = require("../../icons");
var Container = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n  border-bottom: 1px solid ", ";\n  background: ", ";\n  position: sticky;\n  top: 0;\n  height: 44px;\n  flex: 1;\n  z-index: 1;\n  box-sizing: border-box;\n\n  :focus-within {\n    border-bottom: 1px solid ", ";\n  }\n"], ["\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n  border-bottom: 1px solid ", ";\n  background: ", ";\n  position: sticky;\n  top: 0;\n  height: 44px;\n  flex: 1;\n  z-index: 1;\n  box-sizing: border-box;\n\n  :focus-within {\n    border-bottom: 1px solid ", ";\n  }\n"])), theme_1.getColor('grey', 100), theme_1.getColor('white'), theme_1.getColor('brand', 100));
var SearchContainer = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  flex: 1;\n  align-items: center;\n"], ["\n  display: flex;\n  flex: 1;\n  align-items: center;\n"])));
var SearchInput = styled_components_1.default.input(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  border: none;\n  flex: 1;\n  margin-left: 6px;\n  color: ", ";\n  outline: none;\n\n  ::placeholder {\n    color: ", ";\n  }\n"], ["\n  border: none;\n  flex: 1;\n  margin-left: 6px;\n  color: ", ";\n  outline: none;\n\n  ::placeholder {\n    color: ", ";\n  }\n"])), theme_1.getColor('grey', 140), theme_1.getColor('grey', 120));
var Separator = styled_components_1.default.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  margin-left: 20px;\n  border-left: 1px ", " solid;\n  padding-left: 20px;\n  height: 24px;\n  display: flex;\n"], ["\n  margin-left: 20px;\n  border-left: 1px ", " solid;\n  padding-left: 20px;\n  height: 24px;\n  display: flex;\n"])), theme_1.getColor('grey', 100));
var ResultCount = styled_components_1.default.span(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  white-space: nowrap;\n  color: ", ";\n  margin-left: 10px;\n  line-height: 16px;\n  text-transform: none;\n"], ["\n  white-space: nowrap;\n  color: ", ";\n  margin-left: 10px;\n  line-height: 16px;\n  text-transform: none;\n"])), theme_1.getColor('brand', 100));
var Search = function (_a) {
    var children = _a.children, placeholder = _a.placeholder, title = _a.title, searchValue = _a.searchValue, onSearchChange = _a.onSearchChange;
    return (react_1.default.createElement(Container, null,
        react_1.default.createElement(SearchContainer, null,
            react_1.default.createElement(icons_1.SearchIcon, { size: 20 }),
            react_1.default.createElement(SearchInput, { title: title, placeholder: placeholder, value: searchValue, onChange: function (event) { return onSearchChange(event.target.value); } })),
        children));
};
exports.Search = Search;
Search.ResultCount = ResultCount;
Search.Separator = Separator;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5;
//# sourceMappingURL=Search.js.map
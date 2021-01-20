var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React, { useRef } from 'react';
import styled from 'styled-components';
import { useAutoFocus } from '../hooks';
import { SearchIcon } from 'akeneo-design-system';
import { useTranslate } from '@akeneo-pim-community/legacy';
var Container = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n  border-bottom: 1px solid ", ";\n  background: ", ";\n  position: sticky;\n  top: 0;\n  height: 44px;\n  flex: 1;\n  z-index: 1;\n"], ["\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n  border-bottom: 1px solid ", ";\n  background: ", ";\n  position: sticky;\n  top: 0;\n  height: 44px;\n  flex: 1;\n  z-index: 1;\n"])), function (_a) {
    var theme = _a.theme;
    return theme.color.grey100;
}, function (_a) {
    var theme = _a.theme;
    return theme.color.white;
});
var SearchContainer = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  flex: 1;\n  align-items: center;\n"], ["\n  display: flex;\n  flex: 1;\n  align-items: center;\n"])));
var SearchInput = styled.input(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  border: none;\n  flex: 1;\n  margin-left: 5px;\n  color: ", ";\n  outline: none;\n\n  ::placeholder {\n    color: ", ";\n  }\n"], ["\n  border: none;\n  flex: 1;\n  margin-left: 5px;\n  color: ", ";\n  outline: none;\n\n  ::placeholder {\n    color: ", ";\n  }\n"])), function (_a) {
    var theme = _a.theme;
    return theme.color.grey120;
}, function (_a) {
    var theme = _a.theme;
    return theme.color.grey120;
});
var ResultCount = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  white-space: nowrap;\n  color: ", ";\n  margin-left: 10px;\n  line-height: 16px;\n  text-transform: none;\n"], ["\n  white-space: nowrap;\n  color: ", ";\n  margin-left: 10px;\n  line-height: 16px;\n  text-transform: none;\n"])), function (_a) {
    var theme = _a.theme;
    return theme.color.purple100;
});
var SearchBar = function (_a) {
    var className = _a.className, placeholder = _a.placeholder, count = _a.count, searchValue = _a.searchValue, onSearchChange = _a.onSearchChange;
    var translate = useTranslate();
    var searchFieldRef = useRef(null);
    useAutoFocus(searchFieldRef);
    return (React.createElement(Container, { className: className },
        React.createElement(SearchContainer, null,
            React.createElement(SearchIcon, null),
            React.createElement(SearchInput, { title: translate('pim_common.search'), ref: searchFieldRef, placeholder: placeholder || translate('pim_common.search'), value: searchValue, onChange: function (event) { return onSearchChange(event.target.value); } })),
        React.createElement(ResultCount, null, translate('pim_common.result_count', { itemsCount: count.toString() }, count))));
};
export { SearchBar };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=SearchBar.js.map
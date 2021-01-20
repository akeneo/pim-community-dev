var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React from 'react';
import styled from 'styled-components';
import { LoadingPlaceholderContainer } from '../../LoadingPlaceholder';
var Placeholder = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  width: 200px;\n  height: 34px;\n"], ["\n  width: 200px;\n  height: 34px;\n"])));
var Container = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n    color: ", ";\n    font-size: ", ";\n    line-height: 34px;\n    margin: 0;\n    font-weight: normal;\n    white-space: nowrap;\n    overflow: hidden;\n    text-overflow: ellipsis;\n    flex-grow: 1;\n\n    &:first-letter {\n      text-transform: uppercase;\n    }\n  }\n"], ["\n    color: ", ";\n    font-size: ", ";\n    line-height: 34px;\n    margin: 0;\n    font-weight: normal;\n    white-space: nowrap;\n    overflow: hidden;\n    text-overflow: ellipsis;\n    flex-grow: 1;\n\n    &:first-letter {\n      text-transform: uppercase;\n    }\n  }\n"])), function (_a) {
    var theme = _a.theme;
    return theme.color.purple100;
}, function (_a) {
    var theme = _a.theme;
    return theme.fontSize.title;
});
var Title = function (_a) {
    var children = _a.children, showPlaceholder = _a.showPlaceholder;
    return (React.createElement(Container, null, showPlaceholder ? (React.createElement(LoadingPlaceholderContainer, null,
        React.createElement(Placeholder, null))) : (React.createElement(React.Fragment, null, children))));
};
export { Title };
var templateObject_1, templateObject_2;
//# sourceMappingURL=Title.js.map
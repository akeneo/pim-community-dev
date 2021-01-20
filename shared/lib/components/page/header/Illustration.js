var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React from 'react';
import styled from 'styled-components';
var Container = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  position: relative;\n  width: 142px;\n  height: 142px;\n  border: 1px solid ", ";\n  margin-right: 20px;\n  border-radius: 4px;\n  display: flex;\n  overflow: hidden;\n  flex-basis: 142px;\n  flex-shrink: 0;\n\n  img {\n    max-height: 140px;\n    max-width: 140px;\n    width: auto;\n  }\n"], ["\n  position: relative;\n  width: 142px;\n  height: 142px;\n  border: 1px solid ", ";\n  margin-right: 20px;\n  border-radius: 4px;\n  display: flex;\n  overflow: hidden;\n  flex-basis: 142px;\n  flex-shrink: 0;\n\n  img {\n    max-height: 140px;\n    max-width: 140px;\n    width: auto;\n  }\n"])), function (_a) {
    var theme = _a.theme;
    return theme.color.grey80;
});
var Illustration = function (_a) {
    var src = _a.src, _b = _a.title, title = _b === void 0 ? '' : _b;
    return (React.createElement(Container, null,
        React.createElement("img", { src: src, alt: title })));
};
export { Illustration };
var templateObject_1;
//# sourceMappingURL=Illustration.js.map
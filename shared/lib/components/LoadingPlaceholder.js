var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import styled, { keyframes } from 'styled-components';
var loadingBreath = keyframes(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n    0%{background-position:0 50%}\n    50%{background-position:100% 50%}\n    100%{background-position:0 50%}\n"], ["\n    0%{background-position:0 50%}\n    50%{background-position:100% 50%}\n    100%{background-position:0 50%}\n"])));
var LoadingPlaceholderContainer = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  > * {\n    position: relative;\n    border: none !important;\n    border-radius: 5px;\n    overflow: hidden;\n\n    &:after {\n      animation: ", " 2s infinite;\n      content: '';\n      position: absolute;\n      top: 0;\n      left: 0;\n      width: 100%;\n      height: 100%;\n\n      background: linear-gradient(270deg, #fdfdfd, #eee);\n      background-size: 400% 400%;\n      border-radius: 5px;\n    }\n  }\n"], ["\n  > * {\n    position: relative;\n    border: none !important;\n    border-radius: 5px;\n    overflow: hidden;\n\n    &:after {\n      animation: ", " 2s infinite;\n      content: '';\n      position: absolute;\n      top: 0;\n      left: 0;\n      width: 100%;\n      height: 100%;\n\n      background: linear-gradient(270deg, #fdfdfd, #eee);\n      background-size: 400% 400%;\n      border-radius: 5px;\n    }\n  }\n"])), loadingBreath);
export { LoadingPlaceholderContainer };
var templateObject_1, templateObject_2;
//# sourceMappingURL=LoadingPlaceholder.js.map
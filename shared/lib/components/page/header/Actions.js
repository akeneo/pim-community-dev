var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import styled, { css } from 'styled-components';
var Actions = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n  margin-right: -10px;\n\n  > :not(:first-child) {\n    margin-left: 10px;\n  }\n\n  ", "\n"], ["\n  display: flex;\n  align-items: center;\n  margin-right: -10px;\n\n  > :not(:first-child) {\n    margin-left: 10px;\n  }\n\n  ",
    "\n"])), function (props) {
    return props.userActionVisible && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      border-left: 1px solid ", ";\n      margin-left: 20px;\n      padding-left: 20px;\n    "], ["\n      border-left: 1px solid ", ";\n      margin-left: 20px;\n      padding-left: 20px;\n    "])), function (_a) {
        var theme = _a.theme;
        return theme.color.grey80;
    });
});
export { Actions };
var templateObject_1, templateObject_2;
//# sourceMappingURL=Actions.js.map
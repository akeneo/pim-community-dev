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
exports.Field = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var components_1 = require("../../components");
var theme_1 = require("../../theme");
var hooks_1 = require("../../hooks");
var FieldContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  max-width: ", ";\n"], ["\n  display: flex;\n  flex-direction: column;\n  max-width: ", ";\n"])), function (_a) {
    var fullWidth = _a.fullWidth;
    return (fullWidth ? '100%' : '460px');
});
var LabelContainer = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n  line-height: 16px;\n  margin-bottom: 8px;\n  max-width: 460px;\n"], ["\n  display: flex;\n  align-items: center;\n  line-height: 16px;\n  margin-bottom: 8px;\n  max-width: 460px;\n"])));
var Label = styled_components_1.default.label(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  flex: 1;\n"], ["\n  flex: 1;\n"])));
var Channel = styled_components_1.default.span(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  text-transform: capitalize;\n\n  :not(:last-child) {\n    margin-right: 5px;\n  }\n"], ["\n  text-transform: capitalize;\n\n  :not(:last-child) {\n    margin-right: 5px;\n  }\n"])));
var HelperContainer = styled_components_1.default.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  margin-top: 5px;\n  max-width: 460px;\n"], ["\n  margin-top: 5px;\n  max-width: 460px;\n"])));
var IncompleteBadge = styled_components_1.default.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  border-radius: 50%;\n  background-color: ", ";\n  width: 8px;\n  height: 8px;\n  margin-right: 4px;\n"], ["\n  border-radius: 50%;\n  background-color: ", ";\n  width: 8px;\n  height: 8px;\n  margin-right: 4px;\n"])), theme_1.getColor('yellow', 100));
var Field = react_1.default.forwardRef(function (_a, forwardedRef) {
    var label = _a.label, locale = _a.locale, channel = _a.channel, _b = _a.incomplete, incomplete = _b === void 0 ? false : _b, _c = _a.fullWidth, fullWidth = _c === void 0 ? false : _c, requiredLabel = _a.requiredLabel, children = _a.children, rest = __rest(_a, ["label", "locale", "channel", "incomplete", "fullWidth", "requiredLabel", "children"]);
    var inputId = hooks_1.useId('input_');
    var labelId = hooks_1.useId('label_');
    var decoratedChildren = react_1.default.Children.map(children, function (child) {
        if (react_1.default.isValidElement(child) && child.type === components_1.Helper) {
            return react_1.default.createElement(HelperContainer, null, react_1.default.cloneElement(child, { inline: true }));
        }
        if (react_1.default.isValidElement(child)) {
            return react_1.default.cloneElement(child, { id: inputId, 'aria-labelledby': labelId });
        }
        return null;
    });
    return (react_1.default.createElement(FieldContainer, __assign({ ref: forwardedRef, fullWidth: fullWidth !== null && fullWidth !== void 0 ? fullWidth : false }, rest),
        react_1.default.createElement(LabelContainer, null,
            incomplete && react_1.default.createElement(IncompleteBadge, null),
            react_1.default.createElement(Label, { htmlFor: inputId, id: labelId },
                label,
                requiredLabel && (react_1.default.createElement(react_1.default.Fragment, null,
                    "\u00A0",
                    react_1.default.createElement("em", null, requiredLabel)))),
            channel && react_1.default.createElement(Channel, null, channel),
            locale && ('string' === typeof locale ? react_1.default.createElement(components_1.Locale, { code: locale }) : locale)),
        decoratedChildren));
});
exports.Field = Field;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6;
//# sourceMappingURL=Field.js.map
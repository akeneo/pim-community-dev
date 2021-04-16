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
exports.ProgressIndicator = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var theme_1 = require("../../theme");
var icons_1 = require("../../icons");
var StepCircle = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  justify-content: center;\n  align-items: center;\n  height: 32px;\n  width: 32px;\n  color: ", ";\n  background-color: ", ";\n  border-radius: 50%;\n  border: 1px solid ", ";\n"], ["\n  display: flex;\n  justify-content: center;\n  align-items: center;\n  height: 32px;\n  width: 32px;\n  color: ", ";\n  background-color: ",
    ";\n  border-radius: 50%;\n  border: 1px solid ", ";\n"])), theme_1.getColor('white'), function (_a) {
    var state = _a.state;
    return state === 'todo' ? theme_1.getColor('white') : state === 'inprogress' ? theme_1.getColor('green', 100) : theme_1.getColor('green', 100);
}, function (_a) {
    var state = _a.state;
    return (state !== 'todo' ? 'transparent' : theme_1.getColor('grey', 80));
});
var StepLabel = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  font-size: ", ";\n  font-weight: normal;\n  color: ", ";\n  text-transform: uppercase;\n"], ["\n  font-size: ", ";\n  font-weight: normal;\n  color: ", ";\n  text-transform: uppercase;\n"])), theme_1.getFontSize('small'), theme_1.getColor('grey', 120));
var StepContainer = styled_components_1.default.li(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  width: 100%;\n\n  &:before {\n    display: block;\n    content: ' ';\n    width: calc(100% - 34px);\n    border-bottom-width: 1px;\n    border-bottom-style: ", ";\n    border-bottom-color: ", ";\n    position: relative;\n    left: -50%;\n    top: 17px;\n  }\n"], ["\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  width: 100%;\n\n  &:before {\n    display: block;\n    content: ' ';\n    width: calc(100% - 34px);\n    border-bottom-width: 1px;\n    border-bottom-style: ", ";\n    border-bottom-color: ", ";\n    position: relative;\n    left: -50%;\n    top: 17px;\n  }\n"])), function (_a) {
    var state = _a.state;
    return ('todo' === state ? 'dashed' : 'solid');
}, function (_a) {
    var state = _a.state;
    return ('todo' !== state ? theme_1.getColor('green', 100) : theme_1.getColor('grey', 80));
});
var ProgressIndicatorContainer = styled_components_1.default.ul(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  display: flex;\n  justify-content: space-between;\n\n  ", ":first-child:before {\n    display: none;\n    border: none;\n  }\n"], ["\n  display: flex;\n  justify-content: space-between;\n\n  ", ":first-child:before {\n    display: none;\n    border: none;\n  }\n"])), StepContainer);
var Step = react_1.default.forwardRef(function (_a, forwardedRef) {
    var state = _a.state, children = _a.children, rest = __rest(_a, ["state", "children"]);
    if (undefined === state) {
        throw new Error('ProgressIndicator.Step cannot be used outside a ProgressIndicator component');
    }
    return (react_1.default.createElement(StepContainer, __assign({ "aria-current": 'inprogress' === state ? 'step' : undefined, state: state, ref: forwardedRef }, rest),
        react_1.default.createElement(StepCircle, { "aria-hidden": true, state: state }, 'done' === state && react_1.default.createElement(icons_1.CheckIcon, { size: 24 })),
        react_1.default.createElement(StepLabel, null, children)));
});
var ProgressIndicator = function (_a) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    var currentStepIndex = react_1.default.Children.toArray(children).reduce(function (result, child, index) {
        return react_1.isValidElement(child) && child.type === Step && child.props.current === true ? index : result;
    }, 0);
    var decoratedChildren = react_1.default.Children.map(children, function (child, index) {
        if (!(react_1.isValidElement(child) && child.type === Step)) {
            throw new Error('ProgressIndicator only accepts `ProgressIndicator.Step` elements as children');
        }
        return react_1.default.cloneElement(child, {
            state: index > currentStepIndex ? 'todo' : index < currentStepIndex ? 'done' : 'inprogress',
        });
    });
    return (react_1.default.createElement(ProgressIndicatorContainer, __assign({ "aria-label": "progress" }, rest), decoratedChildren));
};
exports.ProgressIndicator = ProgressIndicator;
Step.displayName = 'ProgressIndicator.Step';
ProgressIndicator.Step = Step;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=ProgressIndicator.js.map
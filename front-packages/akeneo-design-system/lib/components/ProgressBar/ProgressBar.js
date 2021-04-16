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
exports.ProgressBar = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var theme_1 = require("../../theme");
var hooks_1 = require("../../hooks");
var ProgressBarContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  overflow: hidden;\n"], ["\n  overflow: hidden;\n"])));
var progressBarAnimation = styled_components_1.keyframes(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  from { background-position: 0 0; }\n  to { background-position: 20px 0; }\n"], ["\n  from { background-position: 0 0; }\n  to { background-position: 20px 0; }\n"])));
var Header = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: flex;\n  align-items: stretch;\n  flex-direction: row;\n  font-size: ", ";\n  flex-flow: row wrap;\n  margin-bottom: -4px;\n"], ["\n  display: flex;\n  align-items: stretch;\n  flex-direction: row;\n  font-size: ", ";\n  flex-flow: row wrap;\n  margin-bottom: -4px;\n"])), theme_1.getFontSize('default'));
var Title = styled_components_1.default.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  color: ", ";\n  padding-right: 20px;\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  max-width: 100%;\n  flex-grow: 1;\n  margin-bottom: 4px;\n\n  /* When header div is greater than 300px the flex-basic is negative, progress label is on same line */\n  /* When header div is lower than 300px the flex-basic is positive, progress label is move to new line */\n  flex-basis: calc((301px - 100%) * 999);\n"], ["\n  color: ", ";\n  padding-right: 20px;\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  max-width: 100%;\n  flex-grow: 1;\n  margin-bottom: 4px;\n\n  /* When header div is greater than 300px the flex-basic is negative, progress label is on same line */\n  /* When header div is lower than 300px the flex-basic is positive, progress label is move to new line */\n  flex-basis: calc((301px - 100%) * 999);\n"])), theme_1.getColor('grey140'));
var ProgressLabel = styled_components_1.default.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  color: ", ";\n  flex-grow: 0;\n  flex-basis: auto;\n  flex-shrink: 1;\n  margin-bottom: 4px;\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n"], ["\n  color: ", ";\n  flex-grow: 0;\n  flex-basis: auto;\n  flex-shrink: 1;\n  margin-bottom: 4px;\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n"])), theme_1.getColor('grey120'));
var ProgressBarBackground = styled_components_1.default.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  background: ", ";\n  height: ", ";\n  overflow: hidden;\n  position: relative;\n"], ["\n  background: ", ";\n  height: ", ";\n  overflow: hidden;\n  position: relative;\n"])), theme_1.getColor('grey60'), function (props) { return getHeightFromSize(props.size); });
var ProgressBarFill = styled_components_1.default.div.attrs(function (props) { return ({
    style: { width: props.width + "%" },
}); })(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  ", "\n\n  height: 100%;\n  left: 0;\n  position: absolute;\n  top: 0;\n  transition: width 0.3s;\n\n  ", "\n"], ["\n  ",
    "\n\n  height: 100%;\n  left: 0;\n  position: absolute;\n  top: 0;\n  transition: width 0.3s;\n\n  ",
    "\n"])), function (_a) {
    var level = _a.level, light = _a.light;
    return styled_components_1.css(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n    background: ", ";\n  "], ["\n    background: ", ";\n  "])), theme_1.getColorForLevel(level, light ? 60 : 100));
}, function (props) {
    return props.indeterminate && styled_components_1.css(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n      background-image: linear-gradient(\n        315deg,\n        rgba(255, 255, 255, 0.6) 25%,\n        rgba(255, 255, 255, 0.4) 25%,\n        rgba(255, 255, 255, 0.4) 50%,\n        rgba(255, 255, 255, 0.6) 50%,\n        rgba(255, 255, 255, 0.6) 75%,\n        rgba(255, 255, 255, 0.4) 75%,\n        rgba(255, 255, 255, 0.4) 100%\n      );\n      background-size: 20px 20px;\n      transition: width 200ms ease;\n      animation: ", " 1s linear infinite;\n    "], ["\n      background-image: linear-gradient(\n        315deg,\n        rgba(255, 255, 255, 0.6) 25%,\n        rgba(255, 255, 255, 0.4) 25%,\n        rgba(255, 255, 255, 0.4) 50%,\n        rgba(255, 255, 255, 0.6) 50%,\n        rgba(255, 255, 255, 0.6) 75%,\n        rgba(255, 255, 255, 0.4) 75%,\n        rgba(255, 255, 255, 0.4) 100%\n      );\n      background-size: 20px 20px;\n      transition: width 200ms ease;\n      animation: ", " 1s linear infinite;\n    "])), progressBarAnimation);
});
var getHeightFromSize = function (size) {
    switch (size) {
        case 'large':
            return '10px';
        case 'small':
        default:
            return '4px';
    }
};
var computeWidthFromPercent = function (percent) {
    if (percent === 'indeterminate' || percent > 100) {
        return 100;
    }
    if (percent < 0) {
        return 0;
    }
    return percent;
};
var ProgressBar = react_1.default.forwardRef(function (_a, forwardedRef) {
    var level = _a.level, percent = _a.percent, title = _a.title, progressLabel = _a.progressLabel, _b = _a.light, light = _b === void 0 ? false : _b, _c = _a.size, size = _c === void 0 ? 'small' : _c, rest = __rest(_a, ["level", "percent", "title", "progressLabel", "light", "size"]);
    var labelId = hooks_1.useId('label_');
    var progressBarId = hooks_1.useId('progress_');
    var progressBarProps = {};
    if (percent !== 'indeterminate' && isNaN(percent)) {
        percent = 'indeterminate';
    }
    if (percent !== 'indeterminate') {
        progressBarProps['aria-valuenow'] = computeWidthFromPercent(percent);
        progressBarProps['aria-valuemin'] = 0;
        progressBarProps['aria-valuemax'] = 100;
    }
    if (title) {
        progressBarProps['aria-labelledby'] = labelId;
    }
    return (react_1.default.createElement(ProgressBarContainer, __assign({ ref: forwardedRef }, rest),
        (title || progressLabel) && (react_1.default.createElement(Header, null,
            react_1.default.createElement(Title, { title: title, id: labelId, htmlFor: progressBarId }, title),
            react_1.default.createElement(ProgressLabel, { title: progressLabel }, progressLabel))),
        react_1.default.createElement(ProgressBarBackground, __assign({ id: progressBarId, role: "progressbar" }, progressBarProps, { size: size }),
            react_1.default.createElement(ProgressBarFill, { level: level, light: light, indeterminate: percent === 'indeterminate', width: computeWidthFromPercent(percent) }))));
});
exports.ProgressBar = ProgressBar;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9;
//# sourceMappingURL=ProgressBar.js.map
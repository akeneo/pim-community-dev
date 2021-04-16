"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.SubTitle = exports.LabelContainer = exports.PreviewContainer = exports.PreviewCard = exports.PreviewGrid = void 0;
var styled_components_1 = __importDefault(require("styled-components"));
var PreviewGrid = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: grid;\n  grid-template-columns: repeat(auto-fill, ", "px);\n  gap: 16px;\n  margin-bottom: 50px;\n"], ["\n  display: grid;\n  grid-template-columns: repeat(auto-fill, ", "px);\n  gap: 16px;\n  margin-bottom: 50px;\n"])), function (_a) {
    var width = _a.width;
    return width;
});
exports.PreviewGrid = PreviewGrid;
PreviewGrid.defaultProps = {
    width: 140,
};
var PreviewCard = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  height: 100%;\n  text-align: center;\n  border: 1px solid rgba(0, 0, 0, 0.1);\n  box-shadow: rgba(0, 0, 0, 0.1) 0 1px 3px 0;\n  border-radius: 4px;\n"], ["\n  display: flex;\n  flex-direction: column;\n  height: 100%;\n  text-align: center;\n  border: 1px solid rgba(0, 0, 0, 0.1);\n  box-shadow: rgba(0, 0, 0, 0.1) 0 1px 3px 0;\n  border-radius: 4px;\n"])));
exports.PreviewCard = PreviewCard;
var PreviewContainer = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  padding: 20px;\n  color: #a1a9b7;\n  overflow: hidden;\n  border-bottom: 1px solid rgba(0, 0, 0, 0.1);\n"], ["\n  padding: 20px;\n  color: #a1a9b7;\n  overflow: hidden;\n  border-bottom: 1px solid rgba(0, 0, 0, 0.1);\n"])));
exports.PreviewContainer = PreviewContainer;
var LabelContainer = styled_components_1.default.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  padding: 8px 0px;\n  max-width: 100%;\n  white-space: nowrap;\n  word-break: break-word;\n  overflow: hidden;\n  text-overflow: ellipsis;\n"], ["\n  padding: 8px 0px;\n  max-width: 100%;\n  white-space: nowrap;\n  word-break: break-word;\n  overflow: hidden;\n  text-overflow: ellipsis;\n"])));
exports.LabelContainer = LabelContainer;
var SubTitle = styled_components_1.default.h2(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  text-transform: Capitalize;\n"], ["\n  text-transform: Capitalize;\n"])));
exports.SubTitle = SubTitle;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5;
//# sourceMappingURL=PreviewGallery.js.map
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
exports.Dropdown = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var Overlay_1 = require("./Overlay/Overlay");
var Item_1 = require("./Item/Item");
var ItemCollection_1 = require("./ItemCollection/ItemCollection");
var Header_1 = require("./Header/Header");
var Title_1 = require("./Header/Title");
var DropdownContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  position: relative;\n  display: inline-flex;\n"], ["\n  position: relative;\n  display: inline-flex;\n"])));
var Dropdown = function (_a) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    return react_1.default.createElement(DropdownContainer, __assign({}, rest), children);
};
exports.Dropdown = Dropdown;
Overlay_1.Overlay.displayName = 'Dropdown.Overlay';
Header_1.Header.displayName = 'Dropdown.Header';
Title_1.Title.displayName = 'Dropdown.Title';
ItemCollection_1.ItemCollection.displayName = 'Dropdown.ItemCollection';
Item_1.Item.displayName = 'Dropdown.Item';
Dropdown.Overlay = Overlay_1.Overlay;
Dropdown.Header = Header_1.Header;
Dropdown.Item = Item_1.Item;
Dropdown.Title = Title_1.Title;
Dropdown.ItemCollection = ItemCollection_1.ItemCollection;
var templateObject_1;
//# sourceMappingURL=Dropdown.js.map
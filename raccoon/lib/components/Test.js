"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Test = void 0;
var react_1 = __importDefault(require("react"));
var legacy_bridge_1 = require("@akeneo-pim-community/legacy-bridge");
var Test = function () {
    var translate = legacy_bridge_1.useTranslate();
    return react_1.default.createElement("div", null,
        "Coucou ",
        translate('pim_common.close'));
};
exports.Test = Test;
//# sourceMappingURL=Test.js.map
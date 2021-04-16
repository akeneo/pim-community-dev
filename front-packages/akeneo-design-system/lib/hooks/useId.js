"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useId = void 0;
var react_1 = require("react");
var shared_1 = require("../shared");
var useId = function (prefix) {
    if (prefix === void 0) { prefix = ''; }
    var id = react_1.useState("" + prefix + shared_1.uuid())[0];
    return id;
};
exports.useId = useId;
//# sourceMappingURL=useId.js.map
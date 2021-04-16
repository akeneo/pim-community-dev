"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useBooleanState = void 0;
var react_1 = require("react");
var useBooleanState = function (defaultValue) {
    if (defaultValue === void 0) { defaultValue = false; }
    var _a = react_1.useState(defaultValue), value = _a[0], setValue = _a[1];
    var setTrue = react_1.useCallback(function () { return setValue(true); }, []);
    var setFalse = react_1.useCallback(function () { return setValue(false); }, []);
    return [value, setTrue, setFalse];
};
exports.useBooleanState = useBooleanState;
//# sourceMappingURL=useBooleanState.js.map
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useSelection = void 0;
var react_1 = require("react");
var useSelection = function () {
    var _a = react_1.useState(false), checked = _a[0], setChecked = _a[1];
    return { checked: checked, onChange: function () { return setChecked(!checked); } };
};
exports.useSelection = useSelection;
//# sourceMappingURL=useSelection.js.map
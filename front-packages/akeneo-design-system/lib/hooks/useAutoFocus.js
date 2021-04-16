"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useAutoFocus = void 0;
var react_1 = require("react");
var useAutoFocus = function (ref) {
    var focus = react_1.useCallback(function () {
        if (ref.current !== null)
            ref.current.focus();
    }, [ref]);
    react_1.useEffect(focus, []);
    return focus;
};
exports.useAutoFocus = useAutoFocus;
//# sourceMappingURL=useAutoFocus.js.map
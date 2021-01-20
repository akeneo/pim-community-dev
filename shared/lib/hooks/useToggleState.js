import { useCallback, useState } from 'react';
var useToggleState = function (defaultValue) {
    var _a = useState(defaultValue), value = _a[0], setValue = _a[1];
    var setTrue = useCallback(function () { return setValue(true); }, []);
    var setFalse = useCallback(function () { return setValue(false); }, []);
    return [value, setTrue, setFalse];
};
export { useToggleState };
//# sourceMappingURL=useToggleState.js.map
import { useState, useEffect } from 'react';
var useStorageState = function (defaultValue, key) {
    var storageValue = localStorage.getItem(key);
    var _a = useState(null !== storageValue ? JSON.parse(storageValue) : defaultValue), value = _a[0], setValue = _a[1];
    useEffect(function () {
        localStorage.setItem(key, JSON.stringify(value));
    }, [value]);
    return [value, setValue];
};
export { useStorageState };
//# sourceMappingURL=useStorageState.js.map
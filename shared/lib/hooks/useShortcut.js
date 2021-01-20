import { useCallback, useEffect } from 'react';
var useShortcut = function (key, callback) {
    var memoizedCallback = useCallback(function (event) { return (key === event.code ? callback(event) : null); }, [
        key,
        callback,
    ]);
    useEffect(function () {
        document.addEventListener('keydown', memoizedCallback);
        return function () { return document.removeEventListener('keydown', memoizedCallback); };
    }, [memoizedCallback]);
};
export { useShortcut };
//# sourceMappingURL=useShortcut.js.map
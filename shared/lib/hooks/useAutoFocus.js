import { useCallback, useEffect } from 'react';
var useAutoFocus = function (ref) {
    var focus = useCallback(function () {
        if (ref.current !== null)
            ref.current.focus();
    }, [ref]);
    useEffect(focus, []);
    return focus;
};
export { useAutoFocus };
//# sourceMappingURL=useAutoFocus.js.map
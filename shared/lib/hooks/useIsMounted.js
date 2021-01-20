import { useCallback, useEffect, useRef } from 'react';
var useIsMounted = function () {
    var isMountedRef = useRef(true);
    var isMounted = useCallback(function () { return isMountedRef.current; }, []);
    useEffect(function () {
        return function () {
            isMountedRef.current = false;
        };
    }, []);
    return isMounted;
};
export { useIsMounted };
//# sourceMappingURL=useIsMounted.js.map
import { useMemo } from 'react';
import { useRouter } from './useRouter';
var useRoute = function (route, parameters) {
    var generate = useRouter().generate;
    return useMemo(function () { return generate(route, parameters); }, [generate, route, parameters]);
};
export { useRoute };
//# sourceMappingURL=useRoute.js.map
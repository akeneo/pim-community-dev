import { useDependenciesContext } from './useDependenciesContext';
var useNotify = function () {
    var notify = useDependenciesContext().notify;
    if (!notify) {
        throw new Error('[DependenciesContext]: Notify has not been properly initiated');
    }
    return notify;
};
export { useNotify };
//# sourceMappingURL=useNotify.js.map
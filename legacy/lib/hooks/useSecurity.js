import { useDependenciesContext } from './useDependenciesContext';
var useSecurity = function () {
    var security = useDependenciesContext().security;
    if (!security) {
        throw new Error('[DependenciesContext]: Security has not been properly initiated');
    }
    return security;
};
export { useSecurity };
//# sourceMappingURL=useSecurity.js.map
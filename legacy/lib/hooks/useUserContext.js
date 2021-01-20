import { useDependenciesContext } from './useDependenciesContext';
var useUserContext = function () {
    var user = useDependenciesContext().user;
    if (!user) {
        throw new Error('[DependenciesContext]: User Context has not been properly initiated');
    }
    return user;
};
export { useUserContext };
//# sourceMappingURL=useUserContext.js.map
import { useDependenciesContext } from './useDependenciesContext';
var useRouter = function () {
    var router = useDependenciesContext().router;
    if (!router) {
        throw new Error('[DependenciesContext]: Router has not been properly initiated');
    }
    return router;
};
export { useRouter };
//# sourceMappingURL=useRouter.js.map
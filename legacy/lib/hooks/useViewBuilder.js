import { useDependenciesContext } from './useDependenciesContext';
var useViewBuilder = function () {
    var viewBuilder = useDependenciesContext().viewBuilder;
    if (!viewBuilder) {
        throw new Error('[DependenciesContext]: ViewBuilder has not been properly initiated');
    }
    return viewBuilder;
};
export { useViewBuilder };
//# sourceMappingURL=useViewBuilder.js.map
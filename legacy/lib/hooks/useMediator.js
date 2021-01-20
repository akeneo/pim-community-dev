import { useDependenciesContext } from './useDependenciesContext';
var useMediator = function () {
    var mediator = useDependenciesContext().mediator;
    if (!mediator) {
        throw new Error('[DependenciesContext]: Mediator has not been properly initiated');
    }
    return mediator;
};
export { useMediator };
//# sourceMappingURL=useMediator.js.map
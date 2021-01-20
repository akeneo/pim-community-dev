import { useContext } from 'react';
import { DependenciesContext } from '../provider';
var useDependenciesContext = function () {
    var context = useContext(DependenciesContext);
    if (!context) {
        throw new Error("[Context]: You are trying to use 'useContext' outside Provider");
    }
    return context;
};
export { useDependenciesContext };
//# sourceMappingURL=useDependenciesContext.js.map
import { useDependenciesContext } from './useDependenciesContext';
var useTranslate = function () {
    var translate = useDependenciesContext().translate;
    if (!translate) {
        throw new Error('[DependenciesContext]: Translate has not been properly initiated');
    }
    return translate;
};
export { useTranslate };
//# sourceMappingURL=useTranslate.js.map
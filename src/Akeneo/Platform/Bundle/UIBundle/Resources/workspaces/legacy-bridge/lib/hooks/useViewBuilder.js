"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useViewBuilder = void 0;
var useDependenciesContext_1 = require("./useDependenciesContext");
var useViewBuilder = function () {
    var viewBuilder = useDependenciesContext_1.useDependenciesContext().viewBuilder;
    if (!viewBuilder) {
        throw new Error('[DependenciesContext]: ViewBuilder has not been properly initiated');
    }
    return viewBuilder;
};
exports.useViewBuilder = useViewBuilder;
//# sourceMappingURL=useViewBuilder.js.map
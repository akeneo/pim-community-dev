"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useMediator = void 0;
var useDependenciesContext_1 = require("./useDependenciesContext");
var useMediator = function () {
    var mediator = useDependenciesContext_1.useDependenciesContext().mediator;
    if (!mediator) {
        throw new Error('[DependenciesContext]: Mediator has not been properly initiated');
    }
    return mediator;
};
exports.useMediator = useMediator;
//# sourceMappingURL=useMediator.js.map
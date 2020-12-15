"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useUserContext = void 0;
var useDependenciesContext_1 = require("./useDependenciesContext");
var useUserContext = function () {
    var user = useDependenciesContext_1.useDependenciesContext().user;
    if (!user) {
        throw new Error('[DependenciesContext]: User Context has not been properly initiated');
    }
    return user;
};
exports.useUserContext = useUserContext;
//# sourceMappingURL=useUserContext.js.map
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useRouter = void 0;
var useDependenciesContext_1 = require("./useDependenciesContext");
var useRouter = function () {
    var router = useDependenciesContext_1.useDependenciesContext().router;
    if (!router) {
        throw new Error('[DependenciesContext]: Router has not been properly initiated');
    }
    return router;
};
exports.useRouter = useRouter;
//# sourceMappingURL=useRouter.js.map
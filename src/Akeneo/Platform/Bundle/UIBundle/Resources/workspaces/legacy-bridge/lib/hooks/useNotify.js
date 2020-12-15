"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useNotify = void 0;
var useDependenciesContext_1 = require("./useDependenciesContext");
var useNotify = function () {
    var notify = useDependenciesContext_1.useDependenciesContext().notify;
    if (!notify) {
        throw new Error('[DependenciesContext]: Notify has not been properly initiated');
    }
    return notify;
};
exports.useNotify = useNotify;
//# sourceMappingURL=useNotify.js.map
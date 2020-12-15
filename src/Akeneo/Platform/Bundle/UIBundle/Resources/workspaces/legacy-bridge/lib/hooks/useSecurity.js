"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useSecurity = void 0;
var useDependenciesContext_1 = require("./useDependenciesContext");
var useSecurity = function () {
    var security = useDependenciesContext_1.useDependenciesContext().security;
    if (!security) {
        throw new Error('[DependenciesContext]: Security has not been properly initiated');
    }
    return security;
};
exports.useSecurity = useSecurity;
//# sourceMappingURL=useSecurity.js.map
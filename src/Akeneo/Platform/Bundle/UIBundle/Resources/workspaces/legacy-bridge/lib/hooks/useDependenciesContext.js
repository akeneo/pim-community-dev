"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useDependenciesContext = void 0;
var react_1 = require("react");
var provider_1 = require("../provider");
var useDependenciesContext = function () {
    var context = react_1.useContext(provider_1.DependenciesContext);
    if (!context) {
        throw new Error("[Context]: You are trying to use 'useContext' outside Provider");
    }
    return context;
};
exports.useDependenciesContext = useDependenciesContext;
//# sourceMappingURL=useDependenciesContext.js.map
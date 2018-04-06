define(['module-registry'], function(moduleRegistry) {
    return function(moduleName) {
        const module = moduleRegistry(moduleName);

        return module.__esModule && undefined !== module.default ? module.default : module;
    };
});

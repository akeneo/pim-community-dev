define(['module-registry'], function(moduleRegistry) {
    return function(moduleName) {
        return moduleRegistry(moduleName);
    };
});

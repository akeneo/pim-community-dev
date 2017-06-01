define(['paths'], function(paths) {
    return function(moduleName) {
        var modulePath = paths[moduleName]
        var grab = require.context('./dynamic/', true, __contextPlaceholder)
        var moduleFileName = modulePath;

        return grab(moduleFileName)
    }
})

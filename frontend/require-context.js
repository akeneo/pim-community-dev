define([], function() {
    return function(moduleName) {
        var modulePath = __contextPaths[moduleName]
        var grab = require.context('./dynamic/', true, __contextPlaceholder)
        if (!modulePath.endsWith('.js')) modulePath += '.js'

        return grab(modulePath)
    }
})

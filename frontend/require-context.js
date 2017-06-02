define(['paths'], function(paths) {
    return function(moduleName) {
        var modulePath = paths[moduleName]
        var grab = require.context('./dynamic/', true, __contextPlaceholder)
        if (!modulePath.endsWith('.js')) modulePath += '.js'

        return grab(modulePath)
    }
})

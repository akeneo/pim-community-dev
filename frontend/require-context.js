define([], function() {
    return function(moduleName) {
        var modulePath = __contextPaths[moduleName]
        var grab = require.context('./dynamic/', true, __contextPlaceholder)
        modulePath = modulePath.replace(/.js$/, '')

        return grab(modulePath)
    }
})

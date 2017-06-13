define([], function() {
    return function(moduleName) {
        var modulePath = __contextPaths[moduleName]
        var grab = require.context('./dynamic/', true, __contextPlaceholder)

        if (typeof modulePath === 'undefined') {
            console.error('Cannot fetch module', moduleName, ' - it needs to be defined in the requirejs.yml')
        }

        modulePath = modulePath.replace(/.js$/, '')

        return grab(modulePath)
    }
})

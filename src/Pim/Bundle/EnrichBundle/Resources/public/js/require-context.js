define(['paths'], function(paths) {
    return function(moduleName) {
        var modulePath = paths[moduleName]
        var grab = require.context('./src/', true, /^\.\/.*\.js$/)
        
        return grab(modulePath)
    }
})

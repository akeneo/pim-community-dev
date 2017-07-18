define([], function() {
    return function(moduleName) {
        var modulePath = __contextPaths[moduleName];
        if (undefined === modulePath) {
            console.error('Module "' + moduleName + '" not found. Please check your requirejs.yml files.');
        }

        var grab = require.context('./dynamic/', true, __contextPlaceholder)

        modulePath = modulePath.replace(/.js$/, '')

        return grab(modulePath)
    }
})

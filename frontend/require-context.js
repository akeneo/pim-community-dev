
    export default function(moduleName) {
        var modulePath = __contextPaths[moduleName]

        if (undefined === modulePath) {
            console.error('Module "' + moduleName + '" not found. Please check youre requirejs.yml files.')
        }

        var grab = require.context('./dynamic/', true, __contextPlaceholder)

        if (typeof modulePath === 'undefined') {
            console.error('Cannot fetch module', moduleName, ' - it needs to be defined in the requirejs.yml')
        }

        modulePath = modulePath.replace(/.js$/, '')

        return grab(modulePath)
    }

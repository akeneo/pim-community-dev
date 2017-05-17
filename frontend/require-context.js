define(['paths'], function(paths) {
    return function(moduleName) {
        var modulePath = paths[moduleName]

        // Make an array of globs in the webpack config for the 'allowed' dynamic import paths, then convert them to regex and inject the list here
        var grab = require.context('./dynamic/', true, /^.*(pim-enterprise-dev\/src|pim-community-dev\/src).*\.js$/)

        var moduleFileName = modulePath;

        if (!modulePath.endsWith('.js')) {
            moduleFileName = moduleFileName + '.js'
        }

        return grab(moduleFileName)
    }
})

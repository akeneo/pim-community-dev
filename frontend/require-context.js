define(['paths'], function(paths) {
    return function(moduleName) {
        var modulePath = paths[moduleName]

        // @TODO - Make an array of globs in the webpack config for the 'allowed' dynamic import paths, then convert them to regex and inject the list here
        var grab = require.context('./dynamic/', true, /^.*(pim-community-dev.*src\/.*\/Bundle\/.*\.js|pim-enterprise-dev.*src\/.*\/Bundle\/.*\.js|.*\/node_modules\/jquery\/dist\/jquery.js)$/)

        var moduleFileName = modulePath;

        // @TODO - Do the same with the file extension
        if (!modulePath.endsWith('.js')) {
            moduleFileName = moduleFileName + '.js'
        }

        return grab(moduleFileName)
    }
})

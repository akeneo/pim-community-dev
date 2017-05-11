define(['jquery', 'underscore', 'paths'], function ($, _, paths) {
    return function(modules, cb) {
        var resolvedModules = [];
        var requestFetcher = require.context('./src/Pim/Bundle', true, /^\.\/.*\.js$/)

        _.each(modules, function (module) {
            var resolvedModule = requestFetcher(paths[module])
            resolvedModules.push(resolvedModule)
        });
        cb.apply(this, _.toArray(resolvedModules))
    }
})

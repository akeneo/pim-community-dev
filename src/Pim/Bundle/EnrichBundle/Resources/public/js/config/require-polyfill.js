define(['jquery', 'underscore', 'twig-dependencies'], function ($, _, twigDependencies) {
    return function(modules, cb) {
        var resolvedModules = [];
        _.each(modules, function (module) {
            resolvedModules.push(twigDependencies[module])
        });
        cb.apply(this, resolvedModules)
    }
})

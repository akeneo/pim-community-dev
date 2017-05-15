define(['jquery', 'underscore', 'require-context'], function ($, _, requireContext) {

    return function(modules, cb) {
        var resolvedModules = [];

        if (typeof modules === 'string') {
            return requireContext(modules)
        } else {
            _.each(modules, function (module) {
                var resolvedModule = requireContext(module)
                resolvedModules.push(resolvedModule)
            });
        }

        if (cb) {
            cb.apply(this, resolvedModules)
        }
    }
})

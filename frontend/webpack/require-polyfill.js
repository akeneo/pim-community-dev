define(['jquery', 'underscore', 'require-context'], function ($, _, requireContext) {
    /**
     * Provides a polyfill to hijack require() calls in twig templates
     *
     * @param  {String|Array}   modules An array of module names to request, or a string for a single module
     * @param  {Function} cb      The callback to run after fetching the module
     */
    return function(modules, cb) {
        var resolvedModules = [];

        if (typeof modules === 'string') {
            return requireContext(modules);
        } else {
            _.each(modules, function (module) {
                var resolvedModule = requireContext(module);
                resolvedModules.push(resolvedModule);
            });
        }

        if (cb) {
            cb.apply(this, resolvedModules);
        }
    };
});

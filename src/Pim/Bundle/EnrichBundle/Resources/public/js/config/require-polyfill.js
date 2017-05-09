define(['jquery', 'underscore', 'paths'], function ($, _) {
    return function(modules) {
        console.trace();
        console.log('hijack require from twig template', modules)

        _.each(modules, function() {
        })


        return {}
    }
})

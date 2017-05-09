// Generate twig modules instead
// , 'twig-requires'
define(['jquery', 'underscore'], function () {
    return function(modules) {
        console.trace();
        console.log('hijack require from twig template', modules)


        // require(['bundle-loader!.' + form.module], function (Form) {
        //     deferred.resolve(Form);
        // });

        return {}
    }
})

require({
    baseUrl: 'bundles',
    shim: {
        'oro/routes': {
            deps: ['routing'],
            init: function(routing) {
                return routing;
            }
        }
    },
    map: {
        '*': {
            'routing': 'oro/routes'
        },
        'oro/routes': {
            'routing': 'routing'
        }
    },
    paths: {
        'oro/routes': '../js/routes'
    }
});

require(['jquery', 'pim/form-builder'], function ($, formBuilder) {
    formBuilder.build('pim-app')
        .then(function (form) {
            form.setElement($('.app'));
            form.render();
        });
});

'use strict';

define([
        'underscore',
        'pim/remover/base',
        'config',
        'routing'
    ], function (
        _,
        BaseRemover,
        module,
        Routing
    ) {
        return _.extend({}, BaseRemover, {
            /**
             * {@inheritdoc}
             */
            getUrl: function (code) {
                return Routing.generate(module.config(__moduleName).url, {code: code});
            }
        });
    }
);

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
            getUrl: function (id) {
                return Routing.generate(module.config(__moduleName).url, {id: id});
            }
        });
    }
);

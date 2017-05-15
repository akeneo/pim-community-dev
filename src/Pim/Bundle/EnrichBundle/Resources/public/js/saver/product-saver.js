'use strict';

define([
        'underscore',
        'pim/saver/base',
        'config',
        'routing'
    ], function (
        _,
        BaseSaver,
        module,
        Routing
    ) {
        return _.extend({}, BaseSaver, {
            /**
             * {@inheritdoc}
             */
            getUrl: function (id) {
                return Routing.generate(module.config(__moduleName).url, {id: id});
            }
        });
    }
);

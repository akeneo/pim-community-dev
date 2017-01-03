'use strict';

define([
        'underscore',
        'pim/saver/base',
        'module',
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
            getUrl: function (code) {
                return Routing.generate(module.config().url, {code: code});
            }
        });
    }
);

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
        return _.extend(BaseSaver, {
            /**
             * Get the entity url
             * @param {Integer} id
             *
             * @return {String}
             */
            getUrl: function (id) {
                return Routing.generate(module.config().url, {id: id})
            }
        });
    }
);

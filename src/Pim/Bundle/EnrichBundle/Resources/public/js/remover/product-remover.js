'use strict';

define([
        'underscore',
        'pim/remover/base',
        'module',
        'routing'
    ], function (
        _,
        BaseRemover,
        module,
        Routing
    ) {
        return _.extend(BaseRemover, {
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

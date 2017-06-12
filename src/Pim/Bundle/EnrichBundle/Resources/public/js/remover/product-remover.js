'use strict';

define([
        'underscore',
        'pim/remover/base',
        'routing'
    ], function (
        _,
        BaseRemover,
        Routing
    ) {
        return _.extend({}, BaseRemover, {
            /**
             * {@inheritdoc}
             */
            getUrl: function (id) {
                return Routing.generate(__moduleConfig.url, {id: id});
            }
        });
    }
);

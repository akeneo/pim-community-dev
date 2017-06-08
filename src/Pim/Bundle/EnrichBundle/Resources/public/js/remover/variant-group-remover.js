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
            getUrl: function (code) {
                return Routing.generate(__moduleConfig.url, {code: code});
            }
        });
    }
);

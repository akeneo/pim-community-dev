'use strict';

define([
        'underscore',
        'pim/saver/base',
        'routing'
    ], function (
        _,
        BaseSaver,
        Routing
    ) {
        return _.extend({}, BaseSaver, {
            /**
             * {@inheritdoc}
             */
            getUrl: function (id) {
                return Routing.generate(__moduleConfig.url, {id: id});
            }
        });
    }
);

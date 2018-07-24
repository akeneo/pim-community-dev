'use strict';

/**
 * Module to save identifiers mapping.
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
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
            getUrl: function () {
                return Routing.generate(__moduleConfig.url);
            }
        });
    }
);

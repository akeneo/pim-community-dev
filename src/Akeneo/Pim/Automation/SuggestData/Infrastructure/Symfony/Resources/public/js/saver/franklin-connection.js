'use strict';

/**
 * Saves the connection configuration to PIM.ai.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
define([
    'underscore',
    'pim/saver/base',
    'routing'
], (
    _,
    BaseSaver,
    Routing
) => {
    return _.extend({}, BaseSaver, {
        /**
         * {@inheritdoc}
         */
        getUrl: function () {
            return Routing.generate(__moduleConfig.url);
        }
    });
});

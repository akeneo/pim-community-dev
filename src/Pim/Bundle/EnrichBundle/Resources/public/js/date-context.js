'use strict';

define(
    ['jquery', 'routing'],
    ($, Routing) => {
        var contextData = {};

        return {
            /**
             * Fetches data from the back then stores it.
             *
             * @returns {Promise}
             */
            initialize: () => {
                return $.get(Routing.generate('pim_localization_format_date'))
                    .then(response => contextData = response);
            },

            /**
             * Returns the value corresponding to the specified key.
             *
             * @param {String} key
             *
             * @returns {*}
             */
            get: key => contextData[key]
        };
    }
);

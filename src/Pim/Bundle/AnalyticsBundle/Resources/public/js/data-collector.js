define(
    ['jquery', 'underscore', 'routing'],
    function ($, _, Routing) {
        'use strict';

        /**
         * @return {Object}
         */
        return {
            collect: function (route) {
                return $.getJSON(Routing.generate(route));
            }
        };
    }
);

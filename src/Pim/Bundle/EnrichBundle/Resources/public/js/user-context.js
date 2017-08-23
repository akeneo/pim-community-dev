'use strict';

define(
    ['jquery', 'backbone', 'underscore', 'routing'],
    ($, Backbone, _, Routing) => {
        var contextData = {};

        return _.extend({
            /**
             * Fetches data from the back then stores it.
             *
             * @returns {Promise}
             */
            initialize: () => {
                return $.get(Routing.generate('pim_user_user_rest_get_current'))
                    .then(response => contextData = response);
            },

            /**
             * Returns the value corresponding to the specified key.
             *
             * @param {String} key
             *
             * @returns {*}
             */
            get: key => contextData[key],

            /**
             * Sets a new value at the specified key.
             *
             * @param {String} key
             * @param {String} value
             */
            set: function (key, value) {
                contextData[key] = value;

                this.trigger('change:' + key);
            }
        }, Backbone.Events);
    }
);

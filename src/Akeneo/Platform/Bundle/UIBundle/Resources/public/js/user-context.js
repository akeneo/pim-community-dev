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
                    .then(response => {
                        contextData = response;
                        contextData.uiLocale = contextData.user_default_locale;
                        contextData.catalogLocale = contextData.catalog_default_locale;
                        contextData.catalogScope = contextData.catalog_default_scope;
                    });
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
             * @param {Object} options
             */
            set: function (key, value, options) {
                options = options || {};
                contextData[key] = value;

                if (!options.silent) {
                    this.trigger('change:' + key);
                }
            }
        }, Backbone.Events);
    }
);

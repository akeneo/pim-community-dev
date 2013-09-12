define(['backbone'],
    function (Backbone) {
        'use strict';

        return Backbone.Model.extend({
            /** @property */
            idAttribute: 'name',

            /** @property */
            defaults: {
                filters: [],
                sorters: []
            },

            /** @property */
            directions: {
                "ASC": "-1",
                "DESC": "1"
            },

            /**
             * Initializer.
             *
             * @param {Object} data
             * @param {String} data.name required
             */
            initialize: function (data) {
                if (!data.name) {
                    throw new TypeError("'name' is required");
                }
            },

            /**
             * Convert model to format needed for applying greed state
             *
             * @returns {}
             */
            toGridState: function () {
                var sorters = this.get('sorters');
                _.each(sorters, _.bind(function (direction, key) {
                    sorters[key] = this.directions[direction];
                }, this));

                return {
                    filters:  this.get('filters'),
                    sorters:  sorters,
                    gridView: this.get('name')
                };
            }
        });
    }
);

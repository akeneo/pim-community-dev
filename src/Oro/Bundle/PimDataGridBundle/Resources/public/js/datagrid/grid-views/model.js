define(['backbone', 'underscore'],
    function (Backbone, _) {
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
             * @param {Array}  data.sorters
             * @param {Array}  data.filters
             */
            initialize: function (data) {
                if (!data.name) {
                    throw new TypeError("'name' is required");
                }

                _.each(data.sorters, _.bind(function (direction, key) {
                    data.sorters[key] = this.directions[direction];
                }, this));
            },

            /**
             * Convert model to format needed for applying greed state
             *
             * @returns {}
             */
            toGridState: function () {
                return {
                    filters:  this.get('filters'),
                    sorters:  this.get('sorters'),
                    gridView: this.get('name')
                };
            }
        });
    }
);

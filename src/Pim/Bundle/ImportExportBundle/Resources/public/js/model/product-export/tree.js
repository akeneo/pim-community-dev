'use strict';

/**
 * Defines a model that store included and excluded categories of a tree in order to perform queries like
 * categories IN included AND categories NOT IN excluded.
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['underscore', 'backbone'], function (_, Backbone) {

    return Backbone.Model.extend({

        defaults: {
            'included': [],
            'excluded': []
        },

        /**
         * Add a value to the excluded collection
         *
         * @param {string} id
         */
        exclude: function (id) {
            this.set('included', _.without(this.get('included'), id.toString()));

            if (!this.get('excluded').includes(id.toString())) {
                this.set('excluded', this.get('excluded').concat(id.toString()));
            }
        },

        /**
         * Add a value to the included collection
         *
         * @param {string} id
         */
        include: function (id) {
            this.set('excluded', _.without(this.get('excluded'), id.toString()));

            if (!this.get('included').includes(id.toString())) {
                this.set('included', this.get('included').concat(id.toString()));
            }
        },

        /**
         * Empty both included and excluded collections
         */
        clear: function () {
            this.set('included', []);
            this.set('excluded', []);
        }
    });
});

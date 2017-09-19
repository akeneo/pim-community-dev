'use strict';

/**
 * Fetcher for product models
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/product-fetcher',
        'oro/mediator',
        'routing'
    ],
    function (
        $,
        _,
        ProductFetcher,
        mediator,
        Routing
    ) {
        return ProductFetcher.extend({
            childrenListPromises: {},

            /**
             * @param {Object} options
             */
            initialize: function (options) {
                this.childrenListPromises = {};
                this.options = options || {};

                ProductFetcher.prototype.initialize.apply(this, [options]);
            },

            /**
             * Fetch all children of the given parent.
             *
             * @return {Promise}
             */
            fetchChildren: function (parentId) {
                if (!(parentId in this.childrenListPromises)) {
                    if (!_.has(this.options.urls, 'children')) {
                        return $.Deferred().reject().promise();
                    }

                    this.childrenListPromises[parentId] = $.getJSON(
                        Routing.generate(this.options.urls.children), {id: parentId}
                    ).then(_.identity).promise();
                }

                return this.childrenListPromises[parentId];
            },

            /**
             * Clear promises.
             */
            clear: function () {
                this.childrenListPromises = {};
            }
        });
    }
);

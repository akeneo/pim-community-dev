'use strict';

define(
    [
        'pim/base-fetcher',
    ],
    function (
        BaseFetcher,
    ) {
        return BaseFetcher.extend({
            /**
             * @param {Object} options
             */
            initialize: function (options) {
                this.options = options || {};
            },


        })
    }
)

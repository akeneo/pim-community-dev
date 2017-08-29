'use strict';

define(
    [
        'jquery',
        'underscore',
        'pim/base-fetcher',
        'routing'

    ],
    function (
        $,
        _,
        BaseFetcher,
        Routing
    ) {
        return BaseFetcher.extend({
            entityActivatedListPromise: null,
            /**
             * @param {Object} options
             */
            initialize: function (options) {
                this.options = options || {};
            },

            /**
             * Fetch all activated locales.
             *
             * @return {Promise}
             */
            fetchActivated: function () {
                if (!this.entityActivatedListPromise) {
                    if (!_.has(this.options.urls, 'list')) {
                        return $.Deferred().reject().promise();
                    }

                    this.entityActivatedListPromise = $.getJSON(
                        Routing.generate(this.options.urls.list),
                        {activated: true}
                    ).then(_.identity).promise();
                }

                return this.entityActivatedListPromise;
            },

            /**
             * {inheritdoc}
             */
            clear: function () {
                this.entityActivatedListPromise = null;

                BaseFetcher.prototype.clear.apply(this, arguments);
            }
        });
    }
);

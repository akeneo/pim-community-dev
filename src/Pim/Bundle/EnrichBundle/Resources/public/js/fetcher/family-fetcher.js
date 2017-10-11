'use strict';

define([
    'underscore',
    'pim/base-fetcher'
    ],
    function (
        _,
        BaseFetcher
    ) {
        return BaseFetcher.extend({
            /**
             * Fetch attributes available as axes for the given family
             *
             * @param {String} familyCode
             *
             * @return {Promise}
             */
            fetchAvailableAxes: function (familyCode) {
                return this.getJSON(
                    this.options.urls.available_axes,
                    {code: familyCode}
                )
                .then(_.identity)
                .promise();
            }
        });
});
